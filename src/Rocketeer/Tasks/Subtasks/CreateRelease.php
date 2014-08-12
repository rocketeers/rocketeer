<?php
namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\Abstracts\AbstractTask;

class CreateRelease extends AbstractTask
{
	/**
	 * Run the task
	 *
	 * @return string
	 */
	public function execute()
	{
		return $this->strategy->deploy();
	}
}
