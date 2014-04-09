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
 * Runs the Deploy task and then cleans up deprecated releases
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class DeployCommand extends AbstractDeployCommand
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy:deploy';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Deploy the website.';

	/**
	 * Execute the tasks
	 *
	 * @return array
	 */
	public function fire()
	{
		return $this->fireTasksQueue(array(
			'deploy',
			'cleanup',
		));
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array_merge(parent::getOptions(), array(
			array('tests',     't',  InputOption::VALUE_NONE,    'Runs the tests on deploy'),
			array('migrate',   'm',  InputOption::VALUE_NONE,    'Run the migrations'),
			array('seed',      's',  InputOption::VALUE_NONE,    'Seed the database (after migrating it if --migrate)'),
			array('clean-all', null, InputOption::VALUE_NONE,    'Cleanup all but the current release on deploy'),
		));
	}
}
