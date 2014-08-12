<?php
namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\Abstracts\AbstractTask;

class Artisan extends AbstractTask
{
	/**
	 * Run the task
	 *
	 * @return string
	 */
	public function execute()
	{
		$artisan = $this->artisan();
		if (!$artisan->getBinary()) {
			return;
		}

		$seed = (bool) $this->getOption('seed');

		if ($this->getOption('migrate')) {
			return $artisan->runForCurrentRelease('migrate', $seed);
		} elseif ($seed) {
			return $artisan->runForCurrentRelease('seed');
		}
	}
}
