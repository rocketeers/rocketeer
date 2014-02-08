<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Tasks;

use Rocketeer\Traits\Task;

/**
 * Rollback to the previous release, or to a specific one
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Rollback extends Task
{
	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Get previous release
		$rollbackRelease = $this->getRollbackRelease();
		if (!$rollbackRelease) {
			$this->command->error('Rocketeer could not rollback as no releases have yet been deployed');
		}

		// If no release specified, display the available ones
		if (array_get($this->command->option(), 'list')) {
			$releases = $this->releasesManager->getReleases();
			$this->displayReleases();

			// Get actual release name from date
			$rollbackRelease = $this->command->ask('Which one do you want to go back to ? (0)', 0);
			$rollbackRelease = $releases[$rollbackRelease];
		}

		// Rollback release
		$this->command->info('Rolling back to release '.$rollbackRelease);
		$this->updateSymlink($rollbackRelease);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the release to rollback to
	 *
	 * @return integer
	 */
	protected function getRollbackRelease()
	{
		$release = array_get($this->command->argument(), 'release');
		if (!$release) {
			$release = $this->releasesManager->getPreviousRelease();
		}

		return $release;
	}
}
