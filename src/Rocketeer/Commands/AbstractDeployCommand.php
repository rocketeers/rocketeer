<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * An abstract command with various helpers for all
 * subcommands to inherit
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class AbstractDeployCommand extends Command
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
		if (!$this->isInsideLaravel()) {
			return str_replace('deploy:', null, $this->name);
		}

		return $this->name;
	}

	/**
	 * Check if the current command is run in the scope of
	 * Laravel or standalone
	 *
	 * @return boolean
	 */
	public function isInsideLaravel()
	{
		return $this->laravel->bound('artisan');
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

		// Bind command to container
		$this->laravel->instance('rocketeer.command', $this);

		// Run tasks and display timer
		$this->laravel['rocketeer.tasks']->run($tasks, $this);
		$this->line('Execution time: <comment>'.round(microtime(true) - $timerStart, 4). 's</comment>');

		// Remove commmand instance
		unset($this->laravel['rocketeer.command']);
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// CREDENTIALS //////////////////////////
	////////////////////////////////////////////////////////////////////

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
			${$credential} = $this->getCredential($repositoryInfos, $credential);
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
		$availableConnections = $this->laravel['rocketeer.rocketeer']->getAvailableConnections();
		$activeConnections    = $this->laravel['rocketeer.rocketeer']->getConnections();

		if (count($activeConnections) <= 0) {
			$connectionName = $this->ask('No connections have been set, please create one : (production)', 'production');
			$this->storeServerCredentials($availableConnections, $connectionName);
		} else {
			foreach ($activeConnections as $connectionName) {
				$this->storeServerCredentials($availableConnections, $connectionName);
			}
		}
	}

	/**
	 * Verifies and stores credentials for the given connection name
	 *
	 * @param string $connections
	 * @param string $connectionName
	 *
	 * @return void
	 */
	protected function storeServerCredentials($connections, $connectionName)
	{
		// Check for server credentials
		$connection  = array_get($connections, $connectionName, array());
		$credentials = array(
			'host'      => true,
			'username'  => true,
			'password'  => false,
			'keyphrase' => null,
			'key'       => false,
			'agent'     => false
		);

		// Gather credentials
		foreach ($credentials as $credential => $required) {
			${$credential} = $this->getCredential($connection, $credential);
			if ($required and !${$credential}) {
				${$credential} = $this->ask('No '.$credential. ' is set for [' .$connectionName. '], please provide one :');
			}
		}

		// Get password or key
		if (!$password and !$key) {
			$type = $this->ask('No password or SSH key is set for [' .$connectionName. '], which would you use ? [key/password]', 'key');
			if ($type == 'key') {
				$default   = $this->laravel['rocketeer.rocketeer']->getUserHomeFolder().'/.ssh/id_rsa';
				$key       = $this->ask('Please enter the full path to your key (' .$default. ')', $default);
				$keyphrase = $this->ask('If a keyphrase is required, provide it');
			} else {
				$password = $this->ask('Please enter your password');
			}
		}

		// Save credentials
		$credentials = compact(array_keys($credentials));
		$this->laravel['rocketeer.rocketeer']->syncConnectionCredentials($connectionName, $credentials);
	}

	/**
	 * Check if a credential needs to be filled
	 *
	 * @param array   $credentials
	 * @param string  $credential
	 *
	 * @return string
	 */
	protected function getCredential($credentials, $credential)
	{
		$credential = array_get($credentials, $credential);
		if (substr($credential, 0, 1) === '{') {
			return;
		}

		return $credential;
	}
}
