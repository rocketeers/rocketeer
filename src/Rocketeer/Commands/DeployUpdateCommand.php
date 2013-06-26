<?php
namespace Rocketeer\Commands;

/**
 * Updates the remote server without doing a new release
 */
class DeployUpdateCommand extends BaseDeployCommand
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy:update';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Updates the remote server without doing a new release';

	/**
	 * The tasks to execute
	 *
	 * @return array
	 */
	public function fire()
	{
		return $this->fireTasksQueue('Rocketeer\Tasks\Update');
	}

}
