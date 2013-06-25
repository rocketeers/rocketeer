<?php
namespace Rocketeer\Commands;

/**
 * Check if the server is ready to receive the application
 */
class DeployCheckCommand extends BaseDeployCommand
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy:check';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Check if the server is ready to receive the application';

	/**
	 * The tasks to execute
	 *
	 * @return array
	 */
	public function fire()
	{
		return $this->fireTasksQueue('Rocketeer\Tasks\Check');
	}

}
