<?php
namespace Rocketeer;

use Closure;
use Illuminate\Container\Container;
use Rocketeer\Commands\BaseTaskCommand;
use Rocketeer\Traits\Task;

/**
 * Handles the registering of Tasks and their execution
 */
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
	 * The output of the queue
	 *
	 * @var array
	 */
	protected $output = array();

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

		return $this->app['artisan']->add(new BaseTaskCommand($task));
	}

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

	/**
	 * Execute Tasks on the default connection
	 *
	 * @param  string|array|Closure $task
	 *
	 * @return array
	 */
	public function execute($queue)
	{
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
		$this->app['rocketeer.rocketeer']->setConnections($connections);

		$queue = (array) $queue;
		$queue = $this->buildQueue($queue);

		return $this->run($queue);
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// QUEUE /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Run the queue
	 *
	 * Here we will actually process the queue to take into account the
	 * various ways to hook into the queue : Tasks, Closures and Commands
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

		// Get the connections to execute the tasks on
		$connections = (array) $this->app['rocketeer.rocketeer']->getConnections();
		foreach ($connections as $connection) {
			$this->app['rocketeer.rocketeer']->setConnection($connection);

			// Check if we provided a stage
			$stage  = $this->getStage();
			$stages = $this->app['rocketeer.rocketeer']->getStages();
			if ($stage and in_array($stage, $stages)) {
				$stages = array($stage);
			}

			// Run the Tasks on each stage
			if (!empty($stages)) {
				foreach ($stages as $stage) {
					$state = $this->runQueue($queue, $stage);
				}
			} else {
				$state = $this->runQueue($queue);
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
			$this->app['rocketeer.rocketeer']->setStage($currentStage);

			$state = $task->execute();
			$this->output[] = $state;
			if ($state === false) {
				return false;
			}
		}

		return true;
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
			if ($task instanceof Closure or (is_string($task) and !class_exists($task))) {
				$queue[] = $task;
				continue;
			}

			// Else build class and add to queue
			if (!($task instanceof Task)) {
				$task = $this->buildTask($task);
			}

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
		if (is_string($task) and !class_exists($task)) {
			$stringTask = $task;
			$closure = function ($task) use ($stringTask) {
				return $task->runForCurrentRelease($stringTask);
			};

		// If the User provided a Closure
		} elseif ($task instanceof Closure) {
			$closure = $task;
		}

		// Build the ClosureTask
		if (isset($closure)) {
			$task = $this->buildTask('Rocketeer\Tasks\Closure');
			$task->setClosure($closure);
		}

		if (!($task instanceof Task)) {
			$task = $this->buildTask($task);
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
			$this->command
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
		// Recursive call
		if (is_array($task)) {
			foreach ($task as $t) {
				$this->addSurroundingTask($t, $surroundingTask, $position);
			}

			return;
		}

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

		// Then for the class slug
		} else {
			$tasks = array_get($this->tasks, $position.'.'.$task->getSlug());
		}

		return (array) $tasks;
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
		$stage = $this->app['rocketeer.rocketeer']->getOption('stages.default');
		if ($this->command) {
			$stage = $this->command->option('stage') ?: $stage;
		}

		// Return all stages if "all"
		if ($stage == 'all') {
			$stage = null;
		}

		return $stage;
	}
}
