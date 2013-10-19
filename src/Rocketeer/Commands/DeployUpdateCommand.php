<?php
namespace Rocketeer\Commands;

use Symfony\Component\Console\Input\InputOption;

/**
 * Update the remote server without doing a new release
 */
class DeployUpdateCommand extends BaseDeployCommand
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
		return $this->fireTasksQueue('Rocketeer\Tasks\Update');
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
