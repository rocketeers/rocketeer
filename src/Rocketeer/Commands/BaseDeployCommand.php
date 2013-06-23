<?php
namespace Rocketeer\Commands;

use Illuminate\Console\Command;

/**
 * A basic deploy command with helpers
 */
abstract class BaseDeployCommand extends Command
{

	/**
	 * Run the tasks
	 *
	 * @return void
	 */
	abstract public function fire();

	/**
	 * Fire a Tasks Queue
	 *
	 * @param  array $tasks
	 *
	 * @return mixed
	 */
	protected function fireTasksQueue($tasks)
	{
		return $this->laravel['rocketeer.tasks']->run($tasks, $this);
	}

}
