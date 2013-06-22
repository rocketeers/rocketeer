<?php
namespace Rocketeer\Commands;

class DeployRollbackCommand extends DeployCommand
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
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('application', InputArgument::OPTIONAL, 'The name of the application to deploy.'),
			array('release',     InputArgument::OPTIONAL, 'The release to rollback to'),
		);
	}

}