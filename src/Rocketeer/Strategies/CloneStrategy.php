<?php
namespace Rocketeer\Strategies;

use Rocketeer\Abstracts\Strategy;
use Rocketeer\Interfaces\StrategyInterface;

class CloneStrategy extends Strategy implements StrategyInterface
{
	/**
	 * Deploy a new clean copy of the application
	 *
	 * @param string|null $destination
	 *
	 * @return boolean
	 */
	public function deploy($destination = null)
	{
		if (!$destination) {
			$destination = $this->releasesManager->getCurrentReleasePath();
		}

		// Executing checkout
		$this->command->info('Cloning repository in "'.$destination.'"');
		$output = $this->bash->run($this->scm->checkout($destination));

		// Cancel if failed and forget credentials
		$success = $this->bash->checkStatus('Unable to clone the repository', $output) !== false;
		if (!$success) {
			$this->server->forgetValue('credentials');

			return false;
		}

		// Deploy submodules
		if ($this->rocketeer->getOption('scm.submodules')) {
			$this->command->info('Initializing submodules if any');
			$this->bash->runForCurrentRelease($this->scm->submodules());
		}

		return $success;
	}

	/**
	 * Update the latest version of the application
	 *
	 * @param boolean $reset
	 *
	 * @return boolean
	 */
	public function update($reset = true)
	{
		$this->command->info('Pulling changes');
		$tasks = [$this->scm->update()];

		// Reset if requested
		if ($reset) {
			array_unshift($tasks, $this->scm->reset());
		}

		return $this->bash->runForCurrentRelease($tasks);
	}
}
