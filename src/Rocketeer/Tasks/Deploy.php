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
		if (!$this->deploymentsManager->isSetup()) {
			$this->executeTask('Setup');
		}

		// Update current release
		$release = time();
		$this->releasesManager->updateCurrentRelease($release);

		// Clone Git repository
		$this->cloneGitRepository();

		// Run composer
		$this->runComposer();
		if ($this->command->option('tests')) {
			if (!$this->runTests()) {
				$this->executeTask('Rollback');

				$this->command->error('Tests failed, rolling back to previous release');
				return false;
			}
		}

		// Set permissions
		$this->setApplicationPermissions();

		// Run migrations
		if ($this->option('migrate')) {
			$this->runMigrations($this->option('seed'));
		}

		// Synchronize shared folders and files
		$currentRelease = $this->releasesManager->getCurrentReleasePath();
		foreach ($this->rocketeer->getShared() as $file) {
			$this->share($currentRelease.'/'.$file);
		}

		return $this->command->info('Successfully deployed release '.$release);
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

		// Clone release and update symlink
		$branch = $this->rocketeer->getGitBranch();
		$this->cloneRepository($repository, $branch);
		$this->updateSymlink();
	}

	/**
	 * Set permissions for the folders used by the application
	 */
	protected function setApplicationPermissions()
	{
		$this->setPermissions('app/database/production.sqlite');
		$this->setPermissions('app/storage');
		$this->setPermissions('public');
	}

}
