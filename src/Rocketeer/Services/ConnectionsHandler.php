<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Rocketeer\Traits\HasLocator;
use string;

/**
 * Handles, get and return, the various connections/stages
 * and their credentials
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class ConnectionsHandler
{
	use HasLocator;

	/**
	 * The current stage
	 *
	 * @var string
	 */
	protected $stage;

	/**
	 * The current server
	 *
	 * @type integer
	 */
	protected $currentServer = 0;

	/**
	 * The connections to use
	 *
	 * @var array|null
	 */
	protected $connections;

	/**
	 * The current connection
	 *
	 * @var string|null
	 */
	protected $connection;

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// SERVERS ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * @return int
	 */
	public function getServer()
	{
		return $this->currentServer;
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// STAGES ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Set the stage Tasks will execute on
	 *
	 * @param string|null $stage
	 */
	public function setStage($stage)
	{
		$this->stage = $stage;

		// If we do have a stage, cleanup previous events
		if ($stage) {
			$this->tasks->registerConfiguredEvents();
		}
	}

	/**
	 * Get the current stage
	 *
	 * @return string
	 */
	public function getStage()
	{
		return $this->stage;
	}

	/**
	 * Get the various stages provided by the User
	 *
	 * @return string[]
	 */
	public function getStages()
	{
		return $this->rocketeer->getOption('stages.stages');
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// APPLICATION //////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Whether the repository used is using SSH or HTTPS
	 *
	 * @return boolean
	 */
	public function needsCredentials()
	{
		return Str::contains($this->getRepository(), 'https://');
	}

	/**
	 * Get the available connections
	 *
	 * @return string[][]|string[]
	 */
	public function getAvailableConnections()
	{
		// Fetch stored credentials
		$storage = (array) $this->localStorage->get('connections');

		// Merge with defaults from config file
		$configuration = (array) $this->config->get('rocketeer::connections');

		// Fetch from remote file
		$remote = (array) $this->config->get('remote.connections');

		// Merge configurations
		$connections = array_replace_recursive($remote, $configuration, $storage);

		// Unify multiservers
		foreach ($connections as $key => $servers) {
			$servers           = array_get($servers, 'servers', [$servers]);
			$connections[$key] = ['servers' => array_values($servers)];
		}

		return $connections;
	}

	/**
	 * Check if a connection has credentials related to it
	 *
	 * @param  string $connection
	 *
	 * @return boolean
	 */
	public function isValidConnection($connection)
	{
		$available = (array) $this->getAvailableConnections();

		return (bool) array_get($available, $connection.'.servers');
	}

	/**
	 * Get the connection in use
	 *
	 * @return string[]
	 */
	public function getConnections()
	{
		// Get cached resolved connections
		if ($this->connections) {
			return $this->connections;
		}

		// Get all and defaults
		$connections = (array) $this->config->get('rocketeer::default');
		$default     = $this->config->get('remote.default');

		// Remove invalid connections
		$instance    = $this;
		$connections = array_filter($connections, function ($value) use ($instance) {
			return $instance->isValidConnection($value);
		});

		// Return default if no active connection(s) set
		if (empty($connections) and $default) {
			return array($default);
		}

		// Set current connection as default
		$this->connections = $connections;

		return $connections;
	}

	/**
	 * Get the active connection
	 *
	 * @return string
	 */
	public function getConnection()
	{
		// Get cached resolved connection
		if ($this->connection) {
			return $this->connection;
		}

		$connection       = array_get($this->getConnections(), 0);
		$this->connection = $connection;

		return $this->connection;
	}

	/**
	 * Get the credentials for a particular connection
	 *
	 * @param string|null $connection
	 *
	 * @return string[][]
	 */
	public function getConnectionCredentials($connection = null)
	{
		$connection = $connection ?: $this->getConnection();
		$available  = $this->getAvailableConnections();

		return array_get($available, $connection.'.servers');
	}

	/**
	 * Get thecredentials for as server
	 *
	 * @param string|null $connection
	 * @param int         $server
	 *
	 * @return mixed
	 */
	public function getServerCredentials($connection = null, $server = 0)
	{
		$connection = $this->getConnectionCredentials($connection);

		return array_get($connection, $server);
	}

	/**
	 * Sync Rocketeer's credentials with Laravel's
	 *
	 * @param string|null   $connection
	 * @param string[]|null $credentials
	 * @param int           $server
	 */
	public function syncConnectionCredentials($connection = null, array $credentials = array(), $server = 0)
	{
		// Store credentials if any
		if ($credentials) {
			$this->localStorage->set('connections.'.$connection.'.servers.'.$server, $credentials);
		}

		// Get connection
		$connection  = $connection ?: $this->getConnection();
		$credentials = $this->getConnectionCredentials($connection);

		$this->config->set('remote.connections.'.$connection, $credentials);
	}

	/**
	 * Set the active connections
	 *
	 * @param string|string[] $connections
	 */
	public function setConnections($connections)
	{
		if (!is_array($connections)) {
			$connections = explode(',', $connections);
		}

		$this->connections = $connections;
	}

	/**
	 * Set the current connection
	 *
	 * @param string $connection
	 * @param int    $server
	 */
	public function setConnection($connection, $server = 0)
	{
		if (!$this->isValidConnection($connection)) {
			return;
		}

		// Fetch the credentials
		$credentials = $this->getServerCredentials($connection, $server);

		// Set the connection
		$this->connection = $connection;
		$this->localStorage     = $server;

		// Register it with SSH component
		$name = $connection.'#'.$server;
		$this->config->set('remote.default', $name);
		$this->config->set('remote.connections.'.$name, $credentials);

		// Update events
		$this->tasks->registerConfiguredEvents();
	}

	/**
	 * Flush active connection(s)
	 */
	public function disconnect()
	{
		$this->connection  = null;
		$this->connections = null;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////// GIT REPOSITORY /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the credentials for the repository
	 *
	 * @return string[]
	 */
	public function getCredentials()
	{
		$credentials = $this->localStorage->get('credentials');
		if (!$credentials) {
			$credentials = $this->rocketeer->getOption('scm');
		}

		// Cast to array
		$credentials = (array) $credentials;

		return array_merge(array(
			'repository' => '',
			'username'   => '',
			'password'   => '',
		), $credentials);
	}

	/**
	 * Get the URL to the Git repository
	 *
	 * @return string
	 */
	public function getRepository()
	{
		// Get credentials
		$repository = $this->getCredentials();
		$username   = array_get($repository, 'username');
		$password   = array_get($repository, 'password');
		$repository = array_get($repository, 'repository');

		// Add credentials if possible
		if ($username or $password) {

			// Build credentials chain
			$credentials = $password ? $username.':'.$password : $username;
			$credentials .= '@';

			// Add them in chain
			$repository = preg_replace('#https://(.+)@#', 'https://', $repository);
			$repository = str_replace('https://', 'https://'.$credentials, $repository);
		}

		return $repository;
	}

	/**
	 * Get the Git branch
	 *
	 * @return string
	 */
	public function getRepositoryBranch()
	{
		exec($this->scm->currentBranch(), $fallback);
		$fallback = Arr::get($fallback, 0, 'master');
		$fallback = trim($fallback);
		$branch   = $this->rocketeer->getOption('scm.branch') ?: $fallback;

		return $branch;
	}
}
