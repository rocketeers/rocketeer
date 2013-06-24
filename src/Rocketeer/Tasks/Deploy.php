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
		if (!$this->deploymentsManager->getValue('is_setup')) {
			$this->executeTask('Rocketeer\Tasks\Setup');
		}

		// Update current release
		$release = time();
		$this->releasesManager->updateCurrentRelease($release);

		// Clone release and update symlink
		$this->cloneRelease();
		$this->removeFolder('current');
		$this->updateSymlink();

		// Run composer
		$this->runComposer();
		if ($this->command->option('phpunit')) {
			if (!$this->runTests()) {
				$this->executeTask('Rocketeer\Tasks\Rollback');

				$this->command->error('Tests failed, rolling back to previous release');
				return false;
			}
		}

		// Set permissions
		$this->setPermissions('app');
		$this->setPermissions('public');

		return $this->command->info('Successfully deployed release '.$release);
	}

}
