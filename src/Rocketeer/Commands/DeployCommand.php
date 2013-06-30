<?php
namespace Rocketeer\Commands;

use Rocketeer\Rocketeer;

/**
 * Your interface to deploying your projects
 */
class DeployCommand extends BaseDeployCommand
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy';

	/**
	 * Displays the current version
	 *
	 * @return string
	 */
	public function fire()
	{
		$this->line('<info>Rocketeer</info> version <comment>'.Rocketeer::VERSION.'</comment>');
	}

}
