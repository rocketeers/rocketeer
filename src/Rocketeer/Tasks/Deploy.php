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
	protected $halting = array(
		'cloneRepository',
		'runComposer',
		'checkTestsResults',
	);

	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Setup if necessary
		if (!$this->isSetup()) {
			$this->command->error('Server is not ready, running Setup task');
			$this->executeTask('Setup');
		}

		// Update current release
		$release = $this->releasesManager->updateCurrentRelease();

		// Run halting methods
		foreach ($this->halting as $method) {
			$this->fireEvent($method);
			if (!$this->$method()) {
				return $this->cancel();
			}
		}

		// Set permissions
		$this->setApplicationPermissions();

		// Run migrations
		$this->runMigrationsAndSeed();

		// Synchronize shared folders and files
		$this->syncSharedFolders();

		// Update symlink
		$this->fireEvent('before-symlink');
		$this->updateSymlink();

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
	 * Cancel deploy
	 *
	 * @return false
	 */
	protected function cancel()
	{
		$this->executeTask('Rollback');

		return false;
	}

	/**
	 * Sync the requested folders and files
	 *
	 * @return void
	 */
	protected function syncSharedFolders()
	{
		$shared = (array) $this->rocketeer->getOption('remote.shared');
		foreach ($shared as $file) {
			$this->share($file);
		}
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
