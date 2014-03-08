<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Traits\BashModules;

/**
 * Repository handling
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Scm extends Binaries
{
	/**
	 * Copies the repository into a release folder and update it
	 *
	 * @param string $destination
	 *
	 * @return string
	 */
	public function copyRepository($destination = null)
	{
		// Get the previous release, if none clone from scratch
		$previous = $this->releasesManager->getPreviousRelease();
		$previous = $this->releasesManager->getPathToRelease($previous);
		if (!$previous) {
			return $this->cloneRepository($destination);
		}

		// Recompute destination
		if (!$destination) {
			$destination = $this->releasesManager->getCurrentReleasePath();
		}

		// Copy old release into new one
		$this->command->info('Copying previous release "' .$previous. '" in "' .$destination. '"');
		$this->copy($previous, $destination);

		// Update repository
		return $this->updateRepository();
	}

	/**
	 * Clone the repo into a release folder
	 *
	 * @param string $destination Where to clone to
	 *
	 * @return string
	 */
	public function cloneRepository($destination = null)
	{
		if (!$destination) {
			$destination = $this->releasesManager->getCurrentReleasePath();
		}

		// Executing checkout
		$this->command->info('Cloning repository in "' .$destination. '"');
		$output = $this->scm->execute('checkout', $destination);
		$this->history[] = $output;

		// Cancel if failed and forget credentials
		$success = $this->checkStatus('Unable to clone the repository', $output) !== false;
		if (!$success) {
			$this->server->forgetValue('credentials');

			return false;
		}

		// Deploy submodules
		if ($this->rocketeer->getOption('scm.submodules')) {
			$this->command->info('Initializing submodules if any');
			$this->runForCurrentRelease($this->scm->submodules());
		}

		return $success;
	}

	/**
	 * Update the current release
	 *
	 * @param boolean $reset Whether the repository should be reset first
	 *
	 * @return string
	 */
	public function updateRepository($reset = true)
	{
		$this->command->info('Pulling changes');
		$tasks = array($this->scm->update());

		// Reset if requested
		if ($reset) {
			array_unshift($tasks, $this->scm->reset());
		}

		return $this->runForCurrentRelease($tasks);
	}
}
