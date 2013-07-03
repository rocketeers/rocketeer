<?php
namespace Rocketeer\Tasks;

use Rocketeer\Tasks\Abstracts\Task;

/**
 * Deploy the website
 */
class Deploy extends Task
{

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
		$release = time();
		$this->releasesManager->updateCurrentRelease($release);

		// Clone Git repository
		if (!$this->cloneGitRepository()) {
			return false;
		}

		// Run Composer
		if (!$this->runComposer()) {
			return false;
		}

		// Run tests
		if ($this->command->option('tests')) {
			if (!$this->runTests()) {
				$this->command->error('Tests failed, rolling back to previous release');
				return false;
			}
		}

		// Set permissions
		$this->setApplicationPermissions();

		// Run migrations
		if ($this->command->option('migrate')) {
			$this->runMigrations($this->command->option('seed'));
		}

		// Synchronize shared folders and files
		$this->syncSharedFolders();

		// Update symlink
		$this->updateSymlink();

		return $this->command->info('Successfully deployed release '.$release);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Sync the requested folders and files
	 *
	 * @return void
	 */
	protected function syncSharedFolders()
	{
		$currentRelease = $this->releasesManager->getCurrentReleasePath();
		foreach ($this->rocketeer->getShared() as $file) {
			$this->share($currentRelease.'/'.$file);
		}
	}

	/**
	 * Clone Git repository
	 *
	 * @return void
	 */
	protected function cloneGitRepository()
	{
		// Get Git credentials
		if (!$this->rocketeer->hasCredentials() and !$this->rocketeer->usesSsh()) {
			$username   = $this->command->ask('What is your Git username ?');
			$password   = $this->command->secret('And your password ?');
			$repository = $this->rocketeer->getGitRepository($username, $password);
		} else {
			$repository = $this->rocketeer->getGitRepository();
		}

		// Clone release
		$branch = $this->rocketeer->getGitBranch();
		return $this->cloneRepository($repository, $branch);
	}

	/**
	 * Set permissions for the folders used by the application
	 *
	 * @return  void
	 */
	protected function setApplicationPermissions()
	{
		$this->setPermissions('app/database/production.sqlite');
		$this->setPermissions('app/storage');
		$this->setPermissions('public');
	}

}
