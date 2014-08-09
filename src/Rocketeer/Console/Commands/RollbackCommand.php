<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Console\Commands;

use Rocketeer\Abstracts\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Rollback to the previous release, or to a specific one
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class RollbackCommand extends AbstractCommand
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
	 * @return integer
	 */
	public function fire()
	{
		return $this->fireTasksQueue('rollback');
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return string[][]
	 */
	protected function getArguments()
	{
		return array(
			['release', InputArgument::OPTIONAL, 'The release to rollback to'],
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return string[][]
	 */
	protected function getOptions()
	{
		return array_merge(parent::getOptions(), array(
			['list', 'L', InputOption::VALUE_NONE, 'Shows the available releases to rollback to'],
		));
	}
}
