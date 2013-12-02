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

		// Get stub of configuration
		$config = __DIR__.'/../../config/config.php';
		$config = file_get_contents($config);

		// Ask for the application name
		$application = $this->command->ask("What is your application's name ?");

		// Replace credentials
		$parameters = array_merge($this->rocketeer->getConnectionCredentials(), array('application_name', $application));
		foreach ($parameters as $name => $value) {
			$config = str_replace('{' .$name. '}', $value, $config);
		}

		// Else copy it at the root
		$root   = trim($this->app['path.base'].'/rocketeer.php', '/');
		$this->app['files']->put($root, $config);

		// Display info
		$folder = basename(dirname($root)).'/'.basename($root);
		$this->command->line('<comment>The Rocketeer configuration was created at</comment> <info>'.$folder.'</info>');

		return $this->history;
	}
}
