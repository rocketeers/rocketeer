<?php
namespace Rocketeer\Commands;

use Symfony\Component\Console\Input\InputOption;

/**
 * Deploy the website
 */
class DeployDeployCommand extends BaseDeployCommand
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
			'Rocketeer\Tasks\Deploy',
			'Rocketeer\Tasks\Cleanup',
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
			array('tests',   't', InputOption::VALUE_NONE, 'Runs the tests on deploy'),
			array('migrate', 'm', InputOption::VALUE_NONE, 'Run the migrations'),
			array('seed',    's', InputOption::VALUE_NONE, 'Seed the database after migrating the database'),
		));
	}
}
