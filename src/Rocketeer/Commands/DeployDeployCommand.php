<?php
namespace Rocketeer\Commands;

class DeployDeployCommand extends BaseDeployCommand
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy:deploy';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Deploy the website.';

	/**
	 * The path to the current release
	 *
	 * @var string
	 */
	protected $currentReleasePath;

	/**
	 * Execute the tasks
	 *
	 * @return array
	 */
	public function fire()
	{
		// Setup if necessary
		if (!$this->getDeploymentsManager()->getValue('is_setup')) {
			$this->call('deploy:setup');
		}

		// Update current release
		$this->getReleasesManager()->updateCurrentRelease(time());
		$this->currentReleasePath = $this->getReleasesManager()->getCurrentReleasePath();

		// Run outstanding tasks
		$this->getRemote()->run(
			$this->getTasks()
		);

		// Cleanup old releases
		$this->call('deploy:cleanup');
	}

	/**
	 * The tasks to execute
	 *
	 * @return array
	 */
	public function tasks()
	{
		return array(
			// Clone release and update symlink
			$this->cloneRelease(),
			$this->removeFolder('current'),
			$this->updateSymlink(),

			// Run composer
			$this->gotoFolder($this->currentReleasePath),
			$this->runComposer(),

			// Set permissions
			$this->setPermissions('app'),
			$this->setPermissions('public'),
			$this->setGroup('app'),
			$this->setGroup('public'),
		);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Set a folder as web-writable
	 *
	 * @param string $folder
	 *
	 * @return  string
	 */
	protected function setPermissions($folder)
	{
		return "chmod -R +x " .$this->currentReleasePath.'/'.$folder;
	}

	protected function setGroup($folder)
	{
		return "chown -R www-data:www-data " .$this->currentReleasePath.'/'.$folder;
	}

}
