<?php
namespace Rocketeer\Commands;

/**
 * Update the remote server without doing a new release
 */
class DeployFlushCommand extends BaseDeployCommand
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy:flush';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Flushes Rocketeer's cache of credentials";

	/**
	 * Execute the tasks
	 *
	 * @return array
	 */
	public function fire()
	{
		$this->laravel['rocketeer.server']->deleteRepository();
		$this->info("Rocketeer's cache has been properly flushed");
	}
}
