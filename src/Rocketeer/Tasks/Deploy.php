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
 * Deploy the website
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Deploy extends Task
{
	/**
	 * Methods that can halt deployment
	 *
	 * @var array
	 */
	protected $halting = array();

	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Create halting events
		$this->createEvents();

		// Setup if necessary
		if (!$this->isSetup()) {
			$this->command->error('Server is not ready, running Setup task');
			$this->executeTask('Setup');
		}

		// Update current release
		$release = $this->releasesManager->updateCurrentRelease();

		// Run halting methods
		foreach ($this->halting as $method) {
			if (!$this->fireEvent($method)) {
				return false;
			}

			if (!$this->$method()) {
				return $this->halt();
			}
		}

		// Set permissions
		$this->setApplicationPermissions();

		// Run migrations
		$this->runMigrationsAndSeed();

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
	/////////////////////////////// SUBTASKS ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Run PHPUnit tests
	 *
	 * @return void
	 */
	protected function checkTestsResults()
	{
		if ($this->getOption('tests') and !$this->runTests()) {
			$this->command->error('Tests failed');

			return false;
		}

		return true;
	}

	/**
	 * Run migrations and seed database
	 *
	 * @return void
	 */
	protected function runMigrationsAndSeed()
	{
		$seed = $this->getOption('seed');

		if ($this->getOption('migrate')) {
			return $this->runMigrations($seed);
		} elseif ($seed) {
			return $this->runSeed();
		}
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Create the events Deploy will run
	 *
	 * @return void
	 */
	protected function createEvents()
	{
		$strategy = $this->rocketeer->getOption('remote.strategy');
		$this->halting = array(
			$strategy.'Repository',
			'runComposer',
			'checkTestsResults',
		);
	}

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
