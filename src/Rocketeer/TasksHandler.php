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

use Illuminate\Container\Container;
use Rocketeer\Commands\BaseTaskCommand;
use Rocketeer\Traits\AbstractLocatorClass;

/**
 * Handles the registering and relating of tasks
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class TasksHandler extends AbstractLocatorClass
{
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

	/**
	 * Delegate methods to TasksQueue for now to
	 * keep public API intact
	 *
	 * @param string $method
	 * @param array  $parameters
	 *
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return call_user_func_array(array($this->queue, $method), $parameters);
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// REGISTRATION /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Register a custom Task with Rocketeer
	 *
	 * @param Task|string $task
	 * @param string      $name
	 *
	 * @return Container
	 */
	public function add($task, $name = null)
	{
		// Build Task if necessary
		$task = $this->buildTask($task, $name);
		$slug = 'rocketeer.tasks.'.$task->getSlug();

		// Add the task to Rocketeer
		$this->app->instance($slug, $task);
		$bound = $this->console->add(new BaseTaskCommand($this->app[$slug]));

		// Bind to Artisan too
		if ($this->app->bound('artisan')) {
			$this->app['artisan']->add(new BaseTaskCommand($task));
		}

		return $bound;
	}

	/**
	 * Register a task with Rocketeer
	 *
	 * @param string $name
	 * @param mixed  $task
	 *
	 * @return void
	 */
	public function task($name, $task)
	{
		return $this->add($task, $name);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// EVENTS /////////////////////////////
	////////////////////////////////////////////////////////////////////

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

		// Get the registered events
		$hooks = (array) $this->rocketeer->getOption('hooks');
		unset($hooks['custom']);

		// Bind events
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
	 * Bind a listener to a task
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
		$event = $this->buildTaskFromClass($task)->getSlug().'.'.$event;
		$event = $this->listenTo($event, $listeners, $priority);

		return $event;
	}

	/**
	 * Get all of a task's listeners
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
		$task   = $this->buildTaskFromClass($task)->getSlug();
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
	/////////////////////////////// PLUGINS ////////////////////////////
	////////////////////////////////////////////////////////////////////

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
		// Build plugin
		if (is_string($plugin)) {
			$plugin = $this->app->make($plugin, array($this->app));
		}

		// Register configuration
		$vendor = $plugin->getNamespace();
		$this->config->package('rocketeer/'.$vendor, $plugin->configurationFolder);
		if ($configuration) {
			$this->config->set($vendor.'::config', $configuration);
		}

		// Bind instances
		$this->app = $plugin->register($this->app);

		// Add hooks to TasksHandler
		$plugin->onQueue($this);
	}
}
