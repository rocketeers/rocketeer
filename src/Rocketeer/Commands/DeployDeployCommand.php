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
		$this->remote->run(array(
			$this->cloneRelease(),
			$this->removeFolder('current'),
			$this->updateSymlink(),
		));

		$this->remote->run(array(
			$this->cd($this->getCurrentRelease()),
			$this->runComposer(),
			$this->runBower(),
			$this->runBasset(),
			"chmod -R +x " .$this->getCurrentRelease().'/app',
			"chmod -R +x " .$this->getCurrentRelease().'/public',
			"chown -R www-data:www-data " .$this->getCurrentRelease().'/app',
			"chown -R www-data:www-data " .$this->getCurrentRelease().'/public',
		));

		$this->call('deploy:cleanup');
	}

}