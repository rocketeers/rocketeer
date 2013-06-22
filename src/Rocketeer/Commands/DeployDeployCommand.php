<?php
namespace Rocketeer\Commands;

class DeployDeployCommand extends DeployCommand
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
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->defineTasks();

		$this->remote->task('cloneRelease');
		$this->remote->task('setupRelease');
	}

}