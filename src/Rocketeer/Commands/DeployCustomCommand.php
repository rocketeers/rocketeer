<?php
namespace Rocketeer\Commands;

use Rocketeer\Rocketeer;
use Rocketeer\Tasks\Task;

/**
 * A basic custom command for Users
 */
class DeployCustomCommand extends BaseDeployCommand
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
	 * @param Task $task
	 */
	public function __construct(Task $task)
	{
		parent::__construct();

		$this->task = $task;
		$this->setName('deploy:'.$task->getSlug());
		$this->setDescription($task->getDescription());
	}

	/**
	 * Fire the custom Task
	 *
	 * @return string
	 */
	public function fire()
	{
		return $this->fireTasksQueue(get_class($this->task));
	}

}
