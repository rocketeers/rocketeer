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
use Illuminate\Container\Container;
use Rocketeer\Commands\BaseTaskCommand;
use Rocketeer\Traits\AbstractLocatorClass;
use Rocketeer\Traits\Task;

/**
 * Handles the registering of Tasks and their execution
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

	/**
	 * The registered events
	 *
	 * @var array
	 */
	protected $registeredEvents = array();

	/**
	 * Build a new TasksQueue Instance
	 *
	 * @param Container  $app
	 */
	public function __construct(Container $app)
	{
		$this->app = $app;
		$this->registerConfiguredEvents();
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////// PUBLIC INTERFACE ////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Register a custom Task with Rocketeer
	 *
	 * @param Task|string    $task
	 *
	 * @return Container
	 */
	public function add($task)
	{
		// Build Task if necessary
		if (is_string($task)) {
			$task = $this->buildTask($task);
		}

		$bound = $this->console->add(new BaseTaskCommand($task));

		// Bind to Artisan too
		if ($this->app->bound('artisan')) {
			$this->app['artisan']->add(new BaseTaskCommand($task));
		}

		return $bound;
	}

	/**
	 * Execute a Task before another one
	 *
	 * @param  string                $task
	 * @param  string|Closure|Task   $listeners
	 * @param  integer               $priority
	 *
	 * @return void
	 */
	public function before($task, $listeners, $priority = 0)
	{
		$this->addTaskListeners($task, 'before', $listeners, $priority);
	}

	/**
	 * Execute a Task after another one
	 *
	 * @param  string                $task
	 * @param  string|Closure|Task   $listeners
	 * @param  integer               $priority
	 *
	 * @return void
	 */
	public function after($task, $listeners, $priority = 0)
	{
		$this->addTaskListeners($task, 'after', $listeners, $priority);
	}

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

	/**
	 * Register a Rocketeer plugin with Rocketeer
	 *
	 * @param string $plugin
	 * @param array  $configuration
	 *
	 * @return void
	 */
	public function plugin($plugin, array $configuration = array())
	{
		// Get plugin name
		$plugin = $this->app->make($plugin, array($this->app));
		$vendor = $plugin->getNamespace();

		// Register configuration
		$this->config->package('rocketeer/'.$vendor, $plugin->configurationFolder);
		if ($configuration) {
			$this->config->set($vendor.'::config', $configuration);
		}

		// Bind instances
		$this->app = $plugin->register($this->app);

		// Add hooks to TasksQueue
		$plugin->onQueue($this);
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

			// If we provided a Closure or a string command, build it
			if ($task instanceof Closure or $this->isStringCommand($task)) {
				$task = $this->buildTaskFromClosure($task);
			}

			// Build remaining tasks
			if (!$task instanceof Task) {
				$task = $this->buildTask($task);
			}

		}

		return $tasks;
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
		$task = $this->buildTask('Rocketeer\Tasks\Closure');
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
	public function buildTask($task)
	{
		if ($task instanceof Task) {
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
	//////////////////////////////// EVENTS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Register with the Dispatcher the events in the configuration
	 *
	 * @return void
	 */
	public function registerConfiguredEvents()
	{
		// Clean previously registered events
		foreach ($this->registeredEvents as $event) {
			$this->events->forget('rocketeer.'.$event);
		}

		// Register new events
		$hooks = (array) $this->rocketeer->getOption('hooks');
		foreach ($hooks as $event => $tasks) {
			foreach ($tasks as $task => $listeners) {
				$this->registeredEvents[] = $this->addTaskListeners($task, $event, $listeners);
			}
		}
	}

	/**
	 * Register listeners for a particular event
	 *
	 * @param string  $event
	 * @param array   $listeners
	 * @param integer $priority
	 *
	 * @return string
	 */
	public function listenTo($event, $listeners, $priority = 0)
	{
		// Create array if it doesn't exist
		$listeners = $this->buildQueue((array) $listeners);

		// Register events
		foreach ($listeners as $listener) {
			$this->events->listen('rocketeer.'.$event, array($listener, 'execute'), $priority);
		}

		return $event;
	}

	/**
	 * Add a Task to surround another Task
	 *
	 * @param string  $task
	 * @param string  $event
	 * @param mixed   $listeners
	 * @param integer $priority
	 */
	public function addTaskListeners($task, $event, $listeners, $priority = 0)
	{
		// Recursive call
		if (is_array($task)) {
			foreach ($task as $t) {
				$this->addTaskListeners($t, $event, $listeners, $priority);
			}

			return;
		}

		// Get event name and register listeners
		$event = $this->buildTask($task)->getSlug().'.'.$event;
		$event = $this->listenTo($event, $listeners, $priority);

		return $event;
	}

	/**
	 * Get the tasks surrounding another Task
	 *
	 * @param  Task    $task
	 * @param  string  $event
	 * @param  boolean $flatten
	 *
	 * @return array
	 */
	public function getTasksListeners($task, $event, $flatten = false)
	{
		// Get events
		$task   = $this->buildTask($task)->getSlug();
		$events = $this->events->getListeners('rocketeer.'.$task.'.'.$event);

		// Flatten the queue if requested
		foreach ($events as $key => $event) {
			$task = $event[0];
			if ($flatten and $task instanceof Tasks\Closure and $stringTask = $task->getStringTask()) {
				$events[$key] = $stringTask;
			}
		}

		return $events;
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
		return is_string($string) and !class_exists($string);
	}
}
