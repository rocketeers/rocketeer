<?php
namespace Rocketeer\Commands;

use Rocketeer\Rocketeer;
use Rocketeer\Tasks\Task;

/**
 * A basic command that only runs one Task
 */
class DeployTaskCommand extends BaseDeployCommand
{

	/**
	 * The default name
	 *
	 * @var string
	 */
	protected $name = 'deploy.custom';

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

		// Set name and description
		$name = $name ?: $task->getSlug();
		$this->setName('deploy:'.$name);
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

}
