<?php
namespace Rocketeer\Commands;

use Illuminate\Console\Command;
use Rocketeer\Rocketeer;

class DeployCommand extends Command
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Your interface to deploying your projects';

	/**
	 * Displays the current version
	 *
	 * @return string
	 */
	public function fire()
	{
		$this->line('<info>Rocketeer</info> version <comment>'.Rocketeer::$version.'</comment>');
	}

}