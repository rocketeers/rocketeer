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

		// Else copy it at the root
		$config = __DIR__.'/../../config/config.php';
		$root   = $this->app['path.base'].'/rocketeer.php';
		$this->app['files']->copy($config, $root);

		// Display info
		$folder = basename(dirname($root)).'/'.basename($root);
		$this->command->line('<comment>The Rocketeer configuration was created at</comment> <info>'.$folder.'</info>');

		return $this->history;
	}
}
