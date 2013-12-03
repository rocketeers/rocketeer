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
		$root   = trim($this->app['path.base'].'/rocketeer.php', '/');
		$this->app['files']->put($root, $this->getConfigurationStub());

		// Display info
		$folder = basename(dirname($root)).'/'.basename($root);
		$this->command->line('<comment>The Rocketeer configuration was created at</comment> <info>'.$folder.'</info>');

		return $this->history;
	}

	/**
	 * Get the configuration stub to use
	 *
	 * @return string
	 */
	protected function getConfigurationStub()
	{
		// Get stub of configuration
		$config = __DIR__.'/../../config/config.php';
		$config = file_get_contents($config);

		// Ask for the application name
		$application = $this->command->ask("What is your application's name ?");

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

		// Replace patterns
		foreach ($parameters as $name => $value) {
			$config = str_replace('{' .$name. '}', $value, $config);
		}

		return $config;
	}
}
