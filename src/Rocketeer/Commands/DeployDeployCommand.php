<?php
namespace Rocketeer\Commands;

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
	 * The path to the current release
	 *
	 * @var string
	 */
	protected $currentReleasePath;

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

}
