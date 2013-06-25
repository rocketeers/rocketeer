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
		return $this->fireTasksQueue('Rocketeer\Tasks\Setup');
	}

}
