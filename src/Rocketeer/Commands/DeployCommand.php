<?php
namespace Rocketeer\Commands;

use Rocketeer\Rocketeer;
use Symfony\Component\Console\Input\InputOption;

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
		// Display version
		if ($this->option('version')) {
			return $this->line('<info>Rocketeer</info> version <comment>'.Rocketeer::VERSION.'</comment>');
		}

		// Deploy
		return $this->fireTasksQueue(array(
			'Rocketeer\Tasks\Deploy',
			'Rocketeer\Tasks\Cleanup',
		));
	}
}
