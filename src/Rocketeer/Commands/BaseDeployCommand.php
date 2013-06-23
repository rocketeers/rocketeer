<?php
namespace Rocketeer\Commands;

use Illuminate\Console\Command;

/**
 * A basic deploy command with helpers
 */
abstract class BaseDeployCommand extends Command
{

	/**
	 * The remote connection
	 *
	 * @var SSH
	 */
	protected $remote;

	/**
	 * Create a new Command instance
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Run the tasks
	 *
	 * @return void
	 */
	abstract public function fire();

	/**
	 * Fire a Tasks Queue
	 *
	 * @param  array $tasks
	 *
	 * @return [type] [description]
	 */
	protected function fireTasksQueue($tasks)
	{
		return $this->laravel['rocketeer.tasks']->run($tasks, $this);
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array();
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array();
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the Remote connection
	 *
	 * @return Connection
	 */
	protected function getRemote()
	{
		// Setup remote connection
		if (!$this->remote) {
			$connections  = $this->laravel['config']->get('rocketeer::connections');
			$this->remote = $this->laravel['remote']->into($connections);
		}

		return $this->remote;
	}

	/**
	 * Get the Rocketeer instance
	 *
	 * @return Rocketeer
	 */
	protected function getRocketeer()
	{
		return $this->laravel['rocketeer.rocketeer'];
	}

	/**
	 * Get the ReleasesManager instance
	 *
	 * @return ReleasesManager
	 */
	protected function getReleasesManager()
	{
		return $this->laravel['rocketeer.releases'];
	}

	/**
	 * Get the DeploymentsManager instance
	 *
	 * @return DeploymentsManager
	 */
	protected function getDeploymentsManager()
	{
		return $this->laravel['rocketeer.deployments'];
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// TASKS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Run Composer on the folder
	 *
	 * @return string
	 */
	protected function runComposer()
	{
		return 'composer install';
	}


}
