<?php
namespace Rocketeer\Commands;

class DeployDeployCommand extends DeployCommand
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
	 * Execute the tasks
	 *
	 * @return array
	 */
	public function fire()
	{
		$this->getReleasesManager()->updateCurrentRelease(time());

		$this->remote->run($this->getTasks());
		$this->call('deploy:cleanup');
	}

	/**
	 * The tasks to execute
	 *
	 * @return array
	 */
	public function tasks()
	{
		$currentReleasePath = $this->getReleasesManager()->getCurrentReleasePath();

		return array(
			$this->cloneRelease(),
			$this->removeFolder('current'),
			$this->updateSymlink(),

			$this->gotoFolder($currentReleasePath),
			$this->runComposer(),
			$this->runBower(),
			$this->runBasset(),
			"chmod -R +x " .$currentReleasePath.'/app',
			"chmod -R +x " .$currentReleasePath.'/public',
			"chown -R www-data:www-data " .$currentReleasePath.'/app',
			"chown -R www-data:www-data " .$currentReleasePath.'/public',
		);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Update the current symlink
	 *
	 * @return string
	 */
	protected function updateSymlink()
	{
		$currentReleasePath = $this->getReleasesManager()->getCurrentReleasePath();
		$currentFolder      = $this->getRocketeer()->getFolder('current');

		return sprintf('ln -s %s %s', $currentReleasePath, $currentFolder);
	}

}