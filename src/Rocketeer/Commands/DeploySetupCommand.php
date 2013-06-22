<?php
namespace Rocketeer\Commands;

/**
 * Set up the remote server for deployment
 */
class DeploySetupCommand extends BaseDeployCommand
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy:setup';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Set up the remote server for deployment';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->remote->run($this->getTasks());

		// Set setup to true
		$this->getDeploymentsManager()->setValue('is_setup', true);

		// Create confirmation message
		$application = $this->getRocketeer()->getApplicationName();
		$homeFolder  = $this->getRocketeer()->getHomeFolder();

		$this->info(sprintf('Successfully setup "%s" at "%s"', $application, $homeFolder));
	}

	/**
	 * The tasks to execute
	 *
	 * @return array
	 */
	protected function tasks()
	{
		return array(
			// Remove existing installation
			$this->removeFolder(),

			// Create base folder and subfolders
			$this->createFolder(),
			$this->createFolder('releases'),
			$this->createFolder('current'),
		);
	}

}
