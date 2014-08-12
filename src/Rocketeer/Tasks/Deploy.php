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
 * Deploy the website
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Deploy extends AbstractTask
{
	/**
	 * Methods that can halt deployment
	 *
	 * @var array
	 */
	protected $halting = array();

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Deploys the website';

	/**
	 * Run the task
	 *
	 * @return  boolean|null
	 */
	public function execute()
	{
		// Setup if necessary
		if (!$this->isSetup()) {
			$this->command->error('Server is not ready, running Setup task');
			$this->executeTask('Setup');
		}

		// Setup the new release
		$release = $this->releasesManager->getNextRelease();


		// Build and execute subtasks
		$tasks = ['CreateRelease', 'Composer'];
		if ($this->getOption('tests')) {
			$tasks[] = 'Phpunit';
		}
		$this->executeTask($tasks);

		// Set permissions
		$this->setApplicationPermissions();

		// Run migrations
		$this->executeTask('Artisan');

		// Synchronize shared folders and files
		$this->syncSharedFolders();

		// Run before-symlink events
		if (!$this->fireEvent('before-symlink')) {
			return $this->halt();
		}

		// Update symlink and mark release as valid
		$this->updateSymlink();
		$this->releasesManager->markReleaseAsValid($release);

		$this->command->info('Successfully deployed release '.$release);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Set permissions for the folders used by the application
	 *
	 * @return  void
	 */
	protected function setApplicationPermissions()
	{
		$files = (array) $this->rocketeer->getOption('remote.permissions.files');
		foreach ($files as $file) {
			$this->setPermissions($file);
		}
	}
}
