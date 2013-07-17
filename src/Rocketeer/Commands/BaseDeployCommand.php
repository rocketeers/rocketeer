<?php
namespace Rocketeer\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * A basic deploy command with helpers
 */
abstract class BaseDeployCommand extends Command
{
	/**
	 * Run the tasks
	 *
	 * @return void
	 */
	abstract public function fire();

  /**
   * Get the console command options.
   *
   * @return array
   */
  protected function getOptions()
  {
    return array(
      array('pretend', 'p', InputOption::VALUE_NONE,     'Returns an array of commands to be executed instead of actually executing them'),
      array('stage',   'S', InputOption::VALUE_REQUIRED, 'The stage to execute the Task in')
    );
  }

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// CORE METHODS /////////////////////////
  ////////////////////////////////////////////////////////////////////

	/**
	 * Fire a Tasks Queue
	 *
	 * @param  string|array $tasks
	 *
	 * @return mixed
	 */
	protected function fireTasksQueue($tasks)
	{
		// Check for credentials
		$this->getServerCredentials();
		$this->getRepositoryCredentials();

		// Start timer
		$timerStart = microtime(true);

		// Convert tasks to array if necessary
		if (!is_array($tasks)) {
			$tasks = array($tasks);
		}

		// Run tasks and display timer
		$this->laravel['rocketeer.tasks']->run($tasks, $this);
		$this->line('Execution time: <comment>'.round(microtime(true) - $timerStart, 4). 's</comment>');
	}

	/**
	 * Get the Repository's credentials
	 *
	 * @return void
	 */
	protected function getRepositoryCredentials()
	{
		// Check for repository credentials
		$repositoryInfos = $this->laravel['rocketeer.rocketeer']->getCredentials();
		$credentials = array('repository');
		if ($this->laravel['rocketeer.rocketeer']->needsCredentials()) {
			$credentials = array('repository', 'username', 'password');
		}

		// Gather credentials
		foreach ($credentials as $credential) {
			${$credential} = array_get($repositoryInfos, $credential);
			if (!${$credential}) {
				${$credential} = $this->ask('No '.$credential. ' is set for the repository, please provide one :');
			}
		}

		// Save them
		$credentials = compact($credentials);
		$this->laravel['rocketeer.server']->setValue('credentials', $credentials);
		foreach ($credentials as $key => $credential) {
			$this->laravel['config']->set('rocketeer::scm.'.$key, $credential);
		}
	}

	/**
	 * Get the Server's credentials
	 *
	 * @return void
	 */
	protected function getServerCredentials()
	{
		// Check for configured connections
		$connections = $this->laravel['rocketeer.rocketeer']->getConnections();
		if (empty($connections)) {
			$connectionName = $this->ask('No connections have been set, please create one : (production)', 'production');
		} else {
			$connectionName = key($connections);
		}

		// Check for server credentials
		$connection  = array_get($connections, $connectionName, array());
		$credentials = array('host', 'username', 'password');

		// Gather credentials
		foreach ($credentials as $credential) {
			${$credential} = array_get($connection, $credential);
			if (!${$credential}) {
				${$credential} = $this->ask('No '.$credential. ' is set for current connection, please provide one :');
			}
		}

		// Save them
		$credentials = compact($credentials);
		$this->laravel['rocketeer.server']->setValue('connections.'.$connectionName, $credentials);
		$this->laravel['config']->set('remote.connections.'.$connectionName, $credentials);
	}
}
