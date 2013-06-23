<?php
namespace Rocketeer\Commands;

use Rocketeer\Tasks\Cleanup;

/**
 * Clean up old releases from the server
 */
class DeployCleanupCommand extends BaseDeployCommand
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy:cleanup';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Clean up old releases from the server';

	/**
	 * The tasks to execute
	 *
	 * @return array
	 */
	public function fire()
	{
		return $this->fireTasksQueue(array(
			'Rocketeer\Tasks\Cleanup',
		));
	}

}
