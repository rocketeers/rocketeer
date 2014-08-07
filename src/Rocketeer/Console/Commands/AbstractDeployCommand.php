<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Console\Commands;

use Closure;
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
	 * @return string[][]
	 */
	protected function getOptions()
	{
		return array(
			['parallel', 'P', InputOption::VALUE_NONE, 'Run the tasks asynchronously instead of sequentially'],
			['pretend', 'p', InputOption::VALUE_NONE, 'Shows which command would execute without actually doing anything'],
			['on', 'C', InputOption::VALUE_REQUIRED, 'The connection(s) to execute the Task in'],
			['stage', 'S', InputOption::VALUE_REQUIRED, 'The stage to execute the Task in']
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
	 * @param string|string[]|\Rocketeer\Abstracts\AbstractTask[] $tasks
	 */
	protected function fireTasksQueue($tasks)
	{
		// Check for credentials
		$this->getServerCredentials();
		$this->getRepositoryCredentials();

		// Convert tasks to array if necessary
		if (!is_array($tasks)) {
			$tasks = array($tasks);
		}

		// Bind command to container
		$this->laravel->instance('rocketeer.command', $this);

		// Run tasks and display timer
		$this->time(function () use ($tasks) {
			$this->laravel['rocketeer.tasks']->run($tasks, $this);
		});

		// Remove command instance
		unset($this->laravel['rocketeer.command']);
	}

	/**
	 * Time an operation and display it afterwards
	 *
	 * @param Closure $callback
	 */
	protected function time(Closure $callback)
	{
		// Start timer, execute callback, close timer
		$timerStart = microtime(true);
		$callback();
		$time = round(microtime(true) - $timerStart, 4);

		$this->line('Execution time: <comment>'.$time.'s</comment>');
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
		$repositoryInfos = $this->laravel['rocketeer.connections']->getCredentials();
		$credentials     = array('repository');
		if (!array_get($repositoryInfos, 'repository') or $this->laravel['rocketeer.connections']->needsCredentials()) {
			$credentials = array('repository', 'username', 'password');
		}

		// Gather credentials
		foreach ($credentials as $credential) {
			${$credential} = $this->getCredential($repositoryInfos, $credential);
			if (!${$credential}) {
				${$credential} = $this->ask('No '.$credential.' is set for the repository, please provide one :');
			}
		}

		// Save them
		$credentials = compact($credentials);
		$this->laravel['rocketeer.storage.local']->set('credentials', $credentials);
		foreach ($credentials as $key => $credential) {
			$this->laravel['config']->set('rocketeer::scm.'.$key, $credential);
		}
	}

	/**
	 * Get the LocalStorage's credentials
	 *
	 * @return void
	 */
	protected function getServerCredentials()
	{
		if ($connections = $this->option('on')) {
			$this->laravel['rocketeer.connections']->setConnections($connections);
		}

		// Check for configured connections
		$availableConnections = $this->laravel['rocketeer.connections']->getAvailableConnections();
		$activeConnections    = $this->laravel['rocketeer.connections']->getConnections();

		if (count($activeConnections) <= 0) {
			$connectionName = $this->ask('No connections have been set, please create one : (production)', 'production');
			$this->storeServerCredentials($availableConnections, $connectionName);
		} else {
			foreach ($activeConnections as $connectionName) {
				$servers = array_get($availableConnections, $connectionName.'.servers');
				$servers = array_keys($servers);
				foreach ($servers as $server) {
					$this->storeServerCredentials($availableConnections, $connectionName, $server);
				}
			}
		}
	}

	/**
	 * Verifies and stores credentials for the given connection name
	 *
	 * @param array        $connections
	 * @param string       $connectionName
	 * @param integer|null $server
	 */
	protected function storeServerCredentials($connections, $connectionName, $server = null)
	{
		// Check for server credentials
		$connection  = $connectionName.'.servers';
		$connection  = !is_null($server) ? $connection.'.'.$server : $connection;
		$connection  = array_get($connections, $connection, array());
		$credentials = array(
			'host'      => true,
			'username'  => true,
			'password'  => false,
			'keyphrase' => null,
			'key'       => false,
			'agent'     => false
		);

		// Update connection name
		$handle = !is_null($server) ? $connectionName.'#'.$server : $connectionName;

		// Gather credentials
		foreach ($credentials as $credential => $required) {
			${$credential} = $this->getCredential($connection, $credential);
			if ($required and !${$credential}) {
				${$credential} = $this->ask('No '.$credential.' is set for ['.$handle.'], please provide one :');
			}
		}

		// Get password or key
		if (!$password and !$key) {
			$type = $this->ask('No password or SSH key is set for ['.$handle.'], which would you use ? [key/password]', 'key');
			if ($type == 'key') {
				$default   = $this->laravel['rocketeer.rocketeer']->getUserHomeFolder().'/.ssh/id_rsa';
				$key       = $this->ask('Please enter the full path to your key ('.$default.')', $default);
				$keyphrase = $this->ask('If a keyphrase is required, provide it');
			} else {
				$password = $this->ask('Please enter your password');
			}
		}

		// Save credentials
		$credentials = compact(array_keys($credentials));
		$this->laravel['rocketeer.connections']->syncConnectionCredentials($connectionName, $credentials, $server);
		$this->laravel['rocketeer.connections']->setConnection($connectionName);
	}

	/**
	 * Check if a credential needs to be filled
	 *
	 * @param array  $credentials
	 * @param string $credential
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
