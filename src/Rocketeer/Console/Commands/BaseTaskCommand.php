<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Console\Commands;

use Rocketeer\Abstracts\AbstractCommand;
use Rocketeer\Abstracts\AbstractTask;

/**
 * A command that wraps around a task class and runs
 * its execute method on fire
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class BaseTaskCommand extends AbstractCommand
{
	/**
	 * The default name
	 *
	 * @var string
	 */
	protected $name = 'deploy:custom';

	/**
	 * the task to execute on fire
	 *
	 * @var AbstractTask
	 */
	protected $task;

	/**
	 * Build a new custom command
	 *
	 * @param AbstractTask $task
	 * @param string|null  $name A name for the command
	 */
	public function __construct(AbstractTask $task, $name = null)
	{
		parent::__construct();

		// Set task
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
	 * @return integer
	 */
	public function fire()
	{
		return $this->fireTasksQueue($this->task->getSlug());
	}

	/**
	 * Get the task this command executes
	 *
	 * @return AbstractTask
	 */
	public function getTask()
	{
		return $this->task;
	}
}
