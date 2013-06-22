<?php
namespace Rocketeer\Commands;

use Symfony\Component\Console\Input\InputArgument;

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
	protected function tasks()
	{
		$rollback = $this->getRollbackRelease();

		return array(
			$this->updateSymlink($rollback),
		);
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

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the release to rollback to
	 *
	 * @return string
	 */
	protected function getRollbackRelease()
	{
		return $this->argument('release') ?: $this->getReleasesManager()->getPreviousRelease();
	}

}
