<?php
namespace Rocketeer\Tasks;

use Rocketeer\Traits\Task;

/**
 * A task to ignite Rocketeer
 */
class Ignite extends Task
{
	 /**
	 * A description of what the Task does
	 *
	 * @var string
	 */
	protected $description = "Creates Rocketeer's configuration";

	/**
	 * Execute ignite
	 *
	 * @return void
	 */
	public function execute()
	{
		// In Laravel, publish the configuration
		if ($this->app->bound('artisan')) {
			return $this->command->call('config:publish', array('package' => 'anahkiasen/rocketeer'));
		}

		// Else create configuration file
		$path = $this->createConfiguration();

		// Display info
		$folder = basename(dirname($path)).'/'.basename($path);
		$this->command->line('<comment>The Rocketeer configuration was created at</comment> <info>'.$folder.'</info>');

		return $this->history;
	}

	/**
	 * Get the configuration stub to use
	 *
	 * @return string
	 */
	protected function createConfiguration()
	{
		// Ask for the application name
		$application = $this->command->ask("What is your application's name ?");

		// Create configuration folder

		// Replace credentials
		$repositoryCredentials = $this->rocketeer->getCredentials();
		$parameters = array_merge(
			$this->rocketeer->getConnectionCredentials(),
			array(
				'scm_repository'   => $repositoryCredentials['repository'],
				'scm_username'     => $repositoryCredentials['username'],
				'scm_password'     => $repositoryCredentials['password'],
				'application_name' => $application,
			)
		);

		$config = $this->app['rocketeer.igniter']->exportConfiguration($parameters);

		// Change repository in use
		$this->app['rocketeer.server']->setRepository($application);

		return $config;
	}
}
