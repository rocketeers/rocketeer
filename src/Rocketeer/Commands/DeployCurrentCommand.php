<?php
namespace Rocketeer\Commands;

/**
 * Displays what the current release is
 */
class DeployCurrentCommand extends BaseDeployCommand
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy:current';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Displays what the current release is';

	/**
	 * The tasks to execute
	 *
	 * @return array
	 */
	public function fire()
	{
		return $this->fireTasksQueue(array(
			'Rocketeer\Tasks\CurrentRelease',
		));
	}

}
