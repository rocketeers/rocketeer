<?php
namespace Rocketeer\Tasks;

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

		// Clone release and update symlink
		$this->cloneRelease();
		$this->updateSymlink();

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
		$this->setPermissions('app');
		$this->setPermissions('public');

		// Run migrations
		$this->runMigrations($this->command->option('seed'));

		// Synchronize shared folders and files
		$currentRelease = $this->releasesManager->getCurrentReleasePath();
		foreach ($this->rocketeer->getShared() as $file) {
			$this->share($currentRelease.'/'.$file);
		}

		return $this->command->info('Successfully deployed release '.$release);
	}

}
