<?php
namespace Rocketeer\Commands;

use Rocketeer\Rocketeer;
use Rocketeer\Traits\Task;

/**
 * A basic command that only runs one Task
 */
class BaseTaskCommand extends BaseDeployCommand
{
	/**
	 * The default name
	 *
	 * @var string
	 */
	protected $name = 'deploy:custom';

	/**
	 * The Task to execute on fire
	 *
	 * @var Task
	 */
	protected $task;

	/**
	 * Build a new custom command
	 *
	 * @param Task   $task
	 * @param string $name  A name for the command
	 */
	public function __construct(Task $task, $name = null)
	{
		parent::__construct();

		// Set Task
		$this->task          = $task;
		$this->task->command = $this;

		// Set name
		$this->name = $name ?: $task->getSlug();
		$this->name = 'deploy:'.$this->name;

		// Set description
		$this->setDescription($task->getDescription());
	}

	/**
	 * Fire the custom Task
	 *
	 * @return string
	 */
	public function fire()
	{
		return $this->fireTasksQueue($this->task);
	}

	/**
	 * Get the Task this command executes
	 *
	 * @return Task
	 */
	public function getTask()
	{
		return $this->task;
	}
}
