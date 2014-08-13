<?php
namespace Rocketeer\Tasks;

use Rocketeer\Abstracts\AbstractTask;

class Migrate extends AbstractTask
{
	/**
	 * Run the task
	 *
	 * @return string
	 */
	public function execute()
	{
		$strategy = $this->getStrategy('Migrate');

		if ($this->getOption('migrate')) {
			$strategy->migrate();
		}

		if ($this->getOption('seed')) {
			$strategy->seed();
		}
	}
}
