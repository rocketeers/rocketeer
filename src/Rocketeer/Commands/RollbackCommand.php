<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Commands;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Rollback to the previous release, or to a specific one
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class RollbackCommand extends AbstractDeployCommand
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
		return $this->fireTasksQueue('rollback');
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

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array_merge(parent::getOptions(), array(
			array('list', 'L', InputOption::VALUE_NONE, 'Shows the available releases to rollback to'),
		));
	}
}
