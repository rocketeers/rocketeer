<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer;

use Closure;
use Rocketeer\Traits\AbstractLocatorClass;
use Rocketeer\Traits\Task;

/**
 * Handles the building and execution of tasks
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class TasksQueue extends AbstractLocatorClass
{
	/**
	 * A list of Tasks to execute
	 *
	 * @var array
	 */
	protected $tasks;

	/**
	 * The Remote connection
	 *
	 * @var Connection
	 */
	protected $remote;

	/**
	 * The output of the queue
	 *
	 * @var array
	 */
	protected $output = array();

	////////////////////////////////////////////////////////////////////
	////////////////////////////// SHORTCUTS ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Execute Tasks on the default connection
	 *
	 * @param  string|array|Closure $queue
	 * @param  string|array         $connections
	 *
	 * @return array
	 */
	public function execute($queue, $connections = null)
	{
		if ($connections) {
			$this->rocketeer->setConnections($connections);
		}

		$queue = (array) $queue;
		$queue = $this->buildQueue($queue);

		return $this->run($queue);
	}

	/**
	 * Execute Tasks on various connections
	 *
	 * @param  string|array         $connections
	 * @param  string|array|Closure $queue
	 *
	 * @return array
	 */
	public function on($connections, $queue)
	{
		return $this->execute($queue, $connections);
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// QUEUE /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Run the queue
	 *
	 * Run an array of Tasks instances on the various
	 * connections and stages provided
	 *
	 * @param  array   $tasks  An array of tasks
	 *
	 * @return array An array of output
	 */
	public function run(array $tasks)
	{
		// First we'll build the queue
		$queue = $this->buildQueue($tasks);

		// Get the connections to execute the tasks on
		$connections = (array) $this->rocketeer->getConnections();
		foreach ($connections as $connection) {
			$this->rocketeer->setConnection($connection);

			// Check if we provided a stage
			$stage  = $this->getStage();
			$stages = $this->rocketeer->getStages();
			if ($stage and in_array($stage, $stages)) {
				$stages = array($stage);
			}

			// Run the Tasks on each stage
			if (!empty($stages)) {
				foreach ($stages as $stage) {
					$this->runQueue($queue, $stage);
				}
			} else {
				$this->runQueue($queue);
			}
		}

		return $this->output;
	}

	/**
	 * Run the queue, taking into account the stage
	 *
	 * @param  array  $tasks
	 * @param  string $stage
	 *
	 * @return boolean
	 */
	protected function runQueue($tasks, $stage = null)
	{
		foreach ($tasks as $task) {
			$currentStage = $task->usesStages() ? $stage : null;
			$this->rocketeer->setStage($currentStage);

			// Here we fire the task and if it was halted
			// at any point, we cancel the whole queue
			$state = $task->fire();
			$this->output[] = $state;
			if ($task->wasHalted() or $state === false) {
				$this->command->error('Deployment was canceled by task "'.$task->getName(). '"');
				return false;
			}
		}

		return true;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// BUILDING ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Build a queue from a list of tasks
	 *
	 * Here we will take the various Tasks names, closures and string tasks
	 * and unify all of those to actual Task instances
	 *
	 * @param  array  $tasks
	 *
	 * @return array
	 */
	public function buildQueue(array $tasks)
	{
		foreach ($tasks as &$task) {
			$task = $this->buildTask($task);
		}

		return $tasks;
	}

	/**
	 * Build a task from anything
	 *
	 * @param mixed  $task
	 * @param string $name
	 *
	 * @return Task
	 */
	public function buildTask($task, $name = null)
	{
		// Check the handle if possible
		if (is_string($task)) {
			$handle = 'rocketeer.tasks.'.$task;
		}

		// If we provided a Closure or a string command, build it
		if ($task instanceof Closure or $this->isStringCommand($task)) {
			$task = $this->buildTaskFromClosure($task);
		}

		// Check for an existing container binding
		elseif (isset($handle) and $this->app->bound($handle)) {
			return $this->app[$handle];
		}

		// Build remaining tasks
		if (!$task instanceof Task) {
			$task = $this->buildTaskFromClass($task);
		}

		// Set the task's name
		if ($name) {
			$task->setName($name);
		}

		return $task;
	}

	/**
	 * Build a Task from a Closure or a string command
	 *
	 * @param  Closure|string $task
	 *
	 * @return Task
	 */
	public function buildTaskFromClosure($task)
	{
		// If the User provided a string to execute
		// We'll build a closure from it
		if ($this->isStringCommand($task)) {
			$stringTask = $task;
			$closure = function ($task) use ($stringTask) {
				return $task->runForCurrentRelease($stringTask);
			};

		// If the User provided a Closure
		} elseif ($task instanceof Closure) {
			$closure = $task;
		}

		// Now that we unified it all to a Closure, we build
		// a Closure Task from there
		$task = $this->buildTaskFromClass('Rocketeer\Tasks\Closure');
		$task->setClosure($closure);

		// If we had an original string used, store it on
		// the task for easier reflection
		if (isset($stringTask)) {
			$task->setStringTask($stringTask);
		}

		return $task;
	}

	/**
	 * Build a Task from its name
	 *
	 * @param  string $task
	 *
	 * @return Task
	 */
	public function buildTaskFromClass($task)
	{
		if (is_object($task) and $task instanceof Task) {
			return $task;
		}

		// Shortcut for calling Rocketeer Tasks
		if (class_exists('Rocketeer\Tasks\\'.ucfirst($task))) {
			$task = 'Rocketeer\Tasks\\'.ucfirst($task);
		}

		// Cancel if class doesn't exist
		if (!class_exists($task)) {
			return $task;
		}

		return new $task($this->app);
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// STAGES ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the stage to execute Tasks in
	 * If null, execute on all stages
	 *
	 * @return string
	 */
	protected function getStage()
	{
		$stage = $this->rocketeer->getOption('stages.default');
		if ($this->hasCommand()) {
			$stage = $this->command->option('stage') ?: $stage;
		}

		// Return all stages if "all"
		if ($stage == 'all') {
			$stage = null;
		}

		return $stage;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Check if a string is a command or a task
	 *
	 * @param string $string
	 *
	 * @return boolean
	 */
	protected function isStringCommand($string)
	{
		return is_string($string) and !class_exists($string) and !$this->app->bound('rocketeer.tasks.'.$string);
	}
}
