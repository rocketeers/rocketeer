<?php
namespace Rocketeer\Commands;

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
		$this->remote->run(array(
			$this->removeFolder(),
		));

		$this->getDeploymentsManager()->deleteDeploymentsFile();

		$this->info('The application was successfully removed from the remote servers');
	}

}
