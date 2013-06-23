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
		$this->releasesManager->updateCurrentRelease(time());

		// Clone release and update symlink
		$this->cloneRelease();
		$this->removeFolder('current');
		$this->updateSymlink();

		// Run composer
		$this->gotoFolder($this->releasesManager->getCurrentReleasePath());
		$this->runComposer();

		// Set permissions
		$this->setPermissions('app');
		$this->setPermissions('public');
	}

}