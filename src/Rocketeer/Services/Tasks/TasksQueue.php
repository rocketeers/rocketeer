<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\Tasks;

use Closure;
use Exception;
use KzykHys\Parallel\Parallel;
use Rocketeer\Abstracts\AbstractTask;
use Rocketeer\Connection;
use Rocketeer\Traits\HasHistory;
use Rocketeer\Traits\HasLocator;

/**
 * Handles the building and execution of tasks
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class TasksQueue
{
	use HasLocator;
	use HasHistory;

	/**
	 * @type Parallel
	 */
	protected $parallel;

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
	 * @param Parallel $parallel
	 */
	public function setParallel($parallel)
	{
		$this->parallel = $parallel;
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////////// SHORTCUTS ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Execute Tasks on the default connection
	 *
	 * @param string|array|Closure $queue
	 * @param string|string[]|null $connections
	 *
	 * @return array
	 */
	public function execute($queue, $connections = null)
	{
		if ($connections) {
			$this->connections->setConnections($connections);
		}

		return $this->run($queue);
	}

	/**
	 * Execute Tasks on various connections
	 *
	 * @param string|string[]      $connections
	 * @param string|array|Closure $queue
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
	 * Run an array of Tasks instances on the various
	 * connections and stages provided
	 *
	 * @param string|array $tasks An array of tasks
	 *
	 * @throws Exception
	 * @return array An array of output
	 */
	public function run($tasks)
	{
		$tasks    = (array) $tasks;
		$queue    = $this->buildQueue($tasks);
		$pipeline = $this->buildPipeline($queue);
		$status   = true;

		// Run pipeline
		if ($this->command->option('parallel')) {
			if (!extension_loaded('pcntl')) {
				throw new Exception('Parallel jobs require the PCNTL extension');
			}

			$this->parallel = $this->parallel ?: new Parallel();
			$this->parallel->run($pipeline);
		} else {
			$key = 0;
			do {
				$status = $pipeline[$key]();
				$key++;
			} while ($status and isset($pipeline[$key]));
		}

		return $status;
	}

	/**
	 * Run the queue, taking into account the stage
	 *
	 * @param Job $job
	 *
	 * @return boolean
	 */
	protected function executeJob(Job $job)
	{
		// Set proper server
		$this->connections->setConnection($job->connection, $job->server);

		foreach ($job->queue as $task) {
			$currentStage = $task->usesStages() ? $job->stage : null;
			$this->connections->setStage($currentStage);

			// Here we fire the task, save its
			// output and return its status
			$state = $task->fire();
			$this->toOutput($state);

			// If the task didn't finish, display what the error was
			if ($task->wasHalted() or $state === false) {
				$this->command->error('The tasks queue was canceled by task "'.$task->getName().'"');

				return false;
			}
		}

		return true;
	}

	/**
	 * Build a pipeline of jobs for Parallel to execute
	 *
	 * @param array $queue
	 *
	 * @return callable[]
	 */
	protected function buildPipeline(array $queue)
	{
		// First we'll build the queue
		$pipeline = [];

		// Get the connections to execute the tasks on
		$connections = (array) $this->connections->getConnections();
		foreach ($connections as $connection) {
			$servers = $this->connections->getConnectionCredentials($connection);
			foreach ($servers as $server => $credentials) {
				// Sanitize stage
				$stage  = $this->getStage();
				$stages = $this->connections->getStages();
				if ($stage and in_array($stage, $stages)) {
					$stages = array($stage);
				}

				// Default to no stages
				if (empty($stages)) {
					$stages = [null];
				}

				// Add job to pipeline
				foreach ($stages as $stage) {
					$pipeline[] = new Job(array(
						'connection' => $connection,
						'server'     => $server,
						'stage'      => $stage,
						'queue'      => $queue,
					));
				}
			}
		}

		// Build pipeline
		foreach ($pipeline as $key => $job) {
			$pipeline[$key] = function () use ($job) {
				return $this->executeJob($job);
			};
		}

		return $pipeline;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// BUILDING ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Build a queue from a list of tasks
	 * Here we will take the various Tasks names, closures and string tasks
	 * and unify all of those to actual AbstractTask instances
	 *
	 * @param array $tasks
	 *
	 * @return array
	 */
	public function buildQueue(array $tasks)
	{
		return array_map([$this, 'buildTask'], $tasks);
	}

	/**
	 * Build a task from anything
	 *
	 * @param string|Closure|AbstractTask $task
	 * @param string|null                 $name
	 *
	 * @return AbstractTask
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
		} // Check for an existing container binding
		elseif (isset($handle) and $this->app->bound($handle)) {
			return $this->app[$handle];
		}

		// Build remaining tasks
		if (!$task instanceof AbstractTask) {
			$task = $this->buildTaskFromClass($task);
		}

		// Set the task's name
		if ($name) {
			$task->setName($name);
		}

		return $task;
	}

	/**
	 * Build a task from a Closure or a string command
	 *
	 * @param Closure|string $task
	 *
	 * @return AbstractTask
	 */
	public function buildTaskFromClosure($task)
	{
		// If the User provided a string to execute
		// We'll build a closure from it
		if ($this->isStringCommand($task)) {
			$stringTask = $task;
			$closure    = function ($task) use ($stringTask) {
				return $task->runForCurrentRelease($stringTask);
			};
			// If the User provided a Closure
		} else {
			$closure = $task;
		}

		// Now that we unified it all to a Closure, we build
		// a Closure AbstractTask from there
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
	 * Build a task from its name
	 *
	 * @param string|AbstractTask $task
	 *
	 * @return AbstractTask|string
	 */
	public function buildTaskFromClass($task)
	{
		if (is_object($task) and $task instanceof AbstractTask) {
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
	 * @param AbstractTask|Closure|string $string
	 *
	 * @return boolean
	 */
	protected function isStringCommand($string)
	{
		return is_string($string) && !class_exists($string) && !$this->app->bound('rocketeer.tasks.'.$string);
	}
}
