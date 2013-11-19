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
			array('on',      'C', InputOption::VALUE_REQUIRED, 'The connection(s) to execute the Task in'),
			array('stage',   'S', InputOption::VALUE_REQUIRED, 'The stage to execute the Task in')
		);
	}

	/**
	 * Returns the command name.
	 *
	 * @return string The command name
	 */
	public function getName()
	{
		// Return commands without namespace if standalone
		if (!isset($this->laravel['events'])) {
			return str_replace('deploy:', null, $this->name);
		}

		return $this->name;
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
		if (!array_get($repositoryInfos, 'repository') or $this->laravel['rocketeer.rocketeer']->needsCredentials()) {
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
		if ($connections = $this->option('on')) {
			$this->laravel['rocketeer.rocketeer']->setConnections($connections);
		}

		// Check for configured connections
		$activeConnections = $this->laravel['rocketeer.rocketeer']->getConnections();

		if (count($activeConnections) <= 0) {
			$connectionName = $this->ask('No connections have been set, please create one : (production)', 'production');
			$this->storeServerCredentials($connectionName);
		} else {
			// Veritfy and store each valid connection
			foreach ($activeConnections as $connectionName) {
				$this->storeServerCredentials($connectionName);
			}
		}
	}

	/**
	 * Verifies and stores credentials for the given connection name
	 *
	 * @param  string $connectionName
	 *
	 * @return void
	 */
	private function storeServerCredentials($connectionName)
	{
		// Check for server credentials
		$connections = $this->laravel['rocketeer.rocketeer']->getAvailableConnections();
		$connection  = array_get($connections, $connectionName, array());
		$credentials = array('host' => true, 'username' => true, 'password' => false, 'keyphrase' => null, 'key' => false);

		// Gather credentials
		foreach ($credentials as $credential => $required) {
			${$credential} = array_get($connection, $credential);
			if (!${$credential} and $required) {
				${$credential} = $this->ask('No '.$credential. ' is set for [' .$connectionName. '], please provide one :');
			}
		}

		// Get password or key
		if (!$password and !$key) {
			$type = $this->ask('No password or SSH key is set for [' .$connectionName. '], which would you use ? [key/password]');
			if ($type == 'key') {
				$key = $this->ask('Please enter the full path to your key');
				$keyphrase = $this->ask('If a keyphrase is required, provide it');
			} else {
				$password = $this->ask('Please enter your password');
			}
		}

		// Save credentials
		$credentials = compact(array_keys($credentials));
		$this->laravel['rocketeer.server']->setValue('connections.'.$connectionName, $credentials);
		$this->laravel['config']->set('remote.connections.'.$connectionName, $credentials);
	}
}
