<?php
namespace Rocketeer;

use Closure;
use Illuminate\Container\Container;
use Rocketeer\Tasks\Task;

class TasksQueue
{

	/**
	 * The IoC Container
	 *
	 * @var Container
	 */
	protected $app;

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
	 * The command executing the TasksQueue
	 *
	 * @var Command
	 */
	protected $command;

	/**
	 * Build a new TasksQueue Instance
	 *
	 * @param Container    $app
	 * @param Command|null $command
	 */
	public function __construct(Container $app, $command = null)
	{
		$this->app     = $app;
		$this->tasks   = $app['config']->get('rocketeer::tasks');
		$this->command = $command;
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////// PUBLIC INTERFACE ////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Execute a Task before another one
	 *
	 * @param  string                $task
	 * @param  string|Closure|Task   $surroundingTask
	 *
	 * @return void
	 */
	public function before($task, $surroundingTask)
	{
		$this->addSurroundingTask($task, $surroundingTask, 'before');
	}

	/**
	 * Execute a Task after another one
	 *
	 * @param  string                $task
	 * @param  string|Closure|Task   $surroundingTask
	 *
	 * @return void
	 */
	public function after($task, $surroundingTask)
	{
		$this->addSurroundingTask($task, $surroundingTask, 'after');
	}

	/**
	 * Get the tasks to execute before a Task
	 *
	 * @param  Task   $task
	 *
	 * @return array
	 */
	public function getBefore(Task $task)
	{
		return $this->getSurroundingTasks($task, 'before');
	}

	/**
	 * Get the tasks to execute after a Task
	 *
	 * @param  Task   $task
	 *
	 * @return array
	 */
	public function getAfter(Task $task)
	{
		return $this->getSurroundingTasks($task, 'after');
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// QUEUE /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Run the queue
	 *
	 * Here we will actually process the queue to take into account the
	 * various ways to hook into the queue : Tasks, Closures and Commands
	 * We will proceed gradually – transform the string commands into Closures
	 * Then transform the Closures into Tasks
	 * That way in the end we have an uniform queue of Tasks
	 *
	 * @param  array   $tasks        An array of tasks
	 * @param  Command $command      The command executing the tasks
	 *
	 * @return array An array of output
	 */
	public function run(array $tasks, $command = null)
	{
		$this->command = $command;
		$queue         = $this->buildQueue($tasks);

		// Finally we execute the Tasks
		foreach ($queue as $task) {
			$state = $task->execute();
			if ($state === false) return $queue;
		}

		return $queue;
	}

	/**
	 * Build a queue from a list of tasks
	 *
	 * Here we will take the various Task names or actual Task instances
	 * provided by the user, get the Tasks to execute before and after
	 * each one, and flatten the whole thing into an actual queue
	 *
	 * @param  array  $tasks
	 *
	 * @return array
	 */
	public function buildQueue(array $tasks)
	{
		$queue = array();
		foreach ($tasks as $task) {

			// If we provided a Closure or a string command, add straight to queue
			if ($task instanceof Closure or is_object($task) or !class_exists($task)) {
				$queue[] = $task;
				continue;
			}

			// Else build class and add to queue
			$task  = $this->buildTask($task);
			$queue = array_merge($queue, $this->getBefore($task), array($task), $this->getAfter($task));
		}

		// Build the tasks provided as Closures/strings
		foreach ($queue as &$task) {
			if (!($task instanceof Task)) {
				$task = $this->buildTaskFromClosure($task);
			}
		}

		return $queue;
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
		if (is_string($task)) {
			$stringTask = $task;
			$closure = function($task) use ($stringTask) {
				return $task->run($stringTask);
			};
		}

		// If the User provided a Closure
		elseif ($task instanceof Closure) {
			$closure = $task;
		}

		// Build the ClosureTask
		if (isset($closure)) {
			$task = $this->buildTask('Rocketeer\Tasks\Closure');
			$task->setClosure($closure);
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
		// Shortcut for calling Rocketeer Tasks
		if (class_exists('Rocketeer\Tasks\\'.$task)) {
			$task = 'Rocketeer\Tasks\\'.$task;
		}

		return new $task(
			$this->app,
			$this,
			$this->getCommand()
		);
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// SURROUNDINGS /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Add a Task to surround another Task
	 *
	 * @param string $task
	 * @param mixed  $surroundingTask
	 * @param string $position        before|after
	 */
	protected function addSurroundingTask($task, $surroundingTask, $position)
	{
		// Create array if it doesn't exist
		if (!array_key_exists($task, $this->tasks[$position])) {
			$this->tasks[$position][$task] = array();
		}

		// Add Task to Tasks
		if (is_array($surroundingTask)) {
			$this->tasks[$position][$task] = array_merge($this->tasks[$position][$task], $surroundingTask);
		} else {
			$this->tasks[$position][$task][] = $surroundingTask;
		}
	}

	/**
	 * Get the tasks surrounding another Task
	 *
	 * @param  Task   $task
	 * @param  string $position     before|after
	 *
	 * @return array
	 */
	protected function getSurroundingTasks(Task $task, $position)
	{
		// First we look for the fully qualified class name
		$key = get_class($task);
		if (array_key_exists($key, $this->tasks[$position])) {
			$tasks = array_get($this->tasks, $position.'.'.$key);
		}

		// Then for the class slug
		else {
			$tasks = array_get($this->tasks, $position.'.'.$task->getSlug());
		}

		return (array) $tasks;
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////////// INSTANCES ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the command executing the Task
	 *
	 * @return Command
	 */
	protected function getCommand()
	{
		return $this->command;
	}

	/**
	 * Get the Remote connection
	 *
	 * @return Connection
	 */
	protected function getRemote()
	{
		// Setup remote connection
		if (!$this->remote) {
			$connections  = $this->app['config']->get('rocketeer::connections');
			$this->remote = $this->app['remote']->into($connections);
		}

		return $this->remote;
	}

	/**
	 * Get the Rocketeer instance
	 *
	 * @return Rocketeer
	 */
	protected function getRocketeer()
	{
		return $this->app['rocketeer.rocketeer'];
	}

	/**
	 * Get the ReleasesManager instance
	 *
	 * @return ReleasesManager
	 */
	protected function getReleasesManager()
	{
		return $this->app['rocketeer.releases'];
	}

	/**
	 * Get the DeploymentsManager instance
	 *
	 * @return DeploymentsManager
	 */
	protected function getDeploymentsManager()
	{
		return $this->app['rocketeer.deployments'];
	}

}
