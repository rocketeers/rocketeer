<?php
namespace Rocketeer\Commands;

use Symfony\Component\Console\Input\InputArgument;

/**
 * Rollback to the previous release, or to a specific one
 */
class DeployRollbackCommand extends BaseDeployCommand
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy:rollback';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Rollback to the previous release, or to a specific one';

	/**
	 * The tasks to execute
	 *
	 * @return array
	 */
	public function fire()
	{
		return $this->fireTasksQueue('Rocketeer\Tasks\Rollback');
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('release', InputArgument::OPTIONAL, 'The release to rollback to'),
		);
	}
}
