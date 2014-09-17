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

use Rocketeer\Abstracts\AbstractTask;

/**
 * Rollback to the previous release, or to a specific one
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Rollback extends AbstractTask
{
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Rollback to the previous release, or to a specific one';

	/**
	 * Run the task
	 *
	 * @return string|null
	 */
	public function execute()
	{
		// Get previous release
		$rollbackRelease = $this->getRollbackRelease();
		if (!$rollbackRelease) {
			return $this->explainer->error('Rocketeer could not rollback as no releases have yet been deployed');
		}

		// If no release specified, display the available ones
		if ($this->command->option('list')) {
			$releases = $this->releasesManager->getReleases();
			$this->displayReleases();

			// Get actual release name from date
			$rollbackRelease = $this->command->askWith('Which one do you want to go back to ?', 0);
			$rollbackRelease = $releases[$rollbackRelease];
		}

		// Rollback release
		$this->explainer->success('Rolling back to release '.$rollbackRelease);
		$this->updateSymlink($rollbackRelease);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the release to rollback to
	 *
	 * @return integer|null
	 */
	protected function getRollbackRelease()
	{
		$release = $this->command->argument('release');
		if (!$release) {
			$release = $this->releasesManager->getPreviousRelease();
		}

		return $release;
	}
}
