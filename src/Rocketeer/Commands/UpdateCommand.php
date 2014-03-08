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

/**
 * Update the remote server without doing a new release
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class UpdateCommand extends AbstractDeployCommand
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
	protected $description = 'Update the remote server without doing a new release.';

	/**
	 * Execute the tasks
	 *
	 * @return array
	 */
	public function fire()
	{
		return $this->fireTasksQueue('update');
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array_merge(parent::getOptions(), array(
			array('migrate', 'm', InputOption::VALUE_NONE, 'Run the migrations'),
			array('seed',    's', InputOption::VALUE_NONE, 'Seed the database after migrating the database'),
		));
	}
}
