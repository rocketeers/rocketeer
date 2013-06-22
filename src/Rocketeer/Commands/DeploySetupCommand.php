<?php
namespace Rocketeer\Commands;

class DeploySetupCommand extends DeployCommand
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy:setup';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Set up the website for deployment';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->remote->run(array(
			$this->removeFolder(),
			$this->createFolder(),
			$this->createFolder('releases'),
			$this->createFolder('current'),
		));

		$this->info('Successfully setup "'.$this->getApplicationName(). '" at "'.$this->getBasePath().'"');
	}

}