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
		// Check if server is ready for deployment
		if (!$this->isSetup()) {
			$this->command->error('Server is not ready, running Setup task');
			$this->executeTask('Setup');
		}

		// Check if local is ready for deployment
		if (!$this->executeTask('Primer')) {
			return $this->halt('Project is not ready for deploy. You were almost fired.');
		}

		// Setup the new release
		$release = $this->releasesManager->getNextRelease();

		// Build subtasks
		$tasks = ['CreateRelease', 'Dependencies'];
		if ($this->getOption('tests')) {
			$tasks[] = 'Test';
		}

		// Create release and set permissions
		$this->steps->executeTask($tasks);
		$this->steps->setApplicationPermissions();

		// Run migrations
		$this->steps->executeTask('Migrate');

		// Synchronize shared folders and files
		$this->steps->syncSharedFolders();

		// Run before-symlink events
		$this->steps->fireEvent('before-symlink');

		// Update symlink
		$this->steps->updateSymlink();

		// Run the steps until one fails
		if (!$this->runSteps()) {
			return $this->halt();
		}

		$this->releasesManager->markReleaseAsValid($release);

		$this->command->info('Successfully deployed release '.$release);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Set permissions for the folders used by the application
	 *
	 * @return true
	 */
	protected function setApplicationPermissions()
	{
		$files = (array) $this->rocketeer->getOption('remote.permissions.files');
		foreach ($files as &$file) {
			$this->setPermissions($file);
		}

		return true;
	}
}
