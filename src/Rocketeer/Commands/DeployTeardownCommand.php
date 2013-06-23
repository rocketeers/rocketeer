<?php
namespace Rocketeer\Commands;

/**
 * Removes the remote applications and existing caches
 */
class DeployTeardownCommand extends BaseDeployCommand
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy:teardown';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Removes the remote applications and existing caches';

	/**
	 * The tasks to execute
	 *
	 * @return array
	 */
	public function fire()
	{
		// Remove remote folders
		$this->remote->run(array(
			$this->removeFolder(),
		));

		// Remove deployments file
		$this->getDeploymentsManager()->deleteDeploymentsFile();

		$this->info('The application was successfully removed from the remote servers');
	}

}
