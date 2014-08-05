<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Support\Str;

/**
 * Handles, get and return, the various connections/stages
 * and their credentials
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class ConnectionsHandler
{
	/**
	 * The IoC Container
	 *
	 * @var Container
	 */
	protected $app;

	/**
	 * The current stage
	 *
	 * @var string
	 */
	protected $stage;

	/**
	 * The connections to use
	 *
	 * @var array
	 */
	protected $connections;

	/**
	 * The current connection
	 *
	 * @var string
	 */
	protected $connection;

	/**
	 * Build a new ReleasesManager
	 *
	 * @param Container $app
	 */
	public function __construct(Container $app)
	{
		$this->app = $app;
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// STAGES ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Set the stage Tasks will execute on
	 *
	 * @param  string $stage
	 *
	 * @return void
	 */
	public function setStage($stage)
	{
		$this->stage = $stage;

		// If we do have a stage, cleanup previous events
		if ($stage) {
			$this->app['rocketeer.tasks']->registerConfiguredEvents();
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
	 * @return array
	 */
	public function getStages()
	{
		return $this->app['rocketeer.rocketeer']->getOption('stages.stages');
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
	 * @param string|null $connection A connection to fetch from the resulting array
	 *
	 * @return array
	 */
	public function getAvailableConnections($connection = null)
	{
		// Fetch stored credentials
		$storage = (array) $this->app['rocketeer.server']->getValue('connections');

		// Merge with defaults from config file
		$configuration = (array) $this->app['config']->get('rocketeer::connections');

		// Fetch from remote file
		$remote = (array) $this->app['config']->get('remote.connections');

		// Merge configurations
		$storage = array_replace_recursive($remote, $configuration, $storage);

		return $connection ? array_get($storage, $connection) : $storage;
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

		return array_key_exists($connection, $available);
	}

	/**
	 * Get the connection in use
	 *
	 * @return string
	 */
	public function getConnections()
	{
		// Get cached resolved connections
		if ($this->connections) {
			return $this->connections;
		}

		// Get all and defaults
		$connections = (array) $this->app['config']->get('rocketeer::default');
		$default     = $this->app['config']->get('remote.default');

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
	 * @param string $connection
	 *
	 * @return array
	 */
	public function getConnectionCredentials($connection = null)
	{
		$connection = $connection ?: $this->getConnection();

		return $this->getAvailableConnections($connection);
	}

	/**
	 * Sync Rocketeer's credentials with Laravel's
	 *
	 * @param string $connection
	 * @param array  $credentials
	 *
	 * @return void
	 */
	public function syncConnectionCredentials($connection = null, array $credentials = array())
	{
		// Store credentials if any
		if ($credentials) {
			$this->app['rocketeer.server']->setValue('connections.'.$connection, $credentials);
		}

		// Get connection
		$connection  = $connection ?: $this->getConnection();
		$credentials = $this->getConnectionCredentials($connection);

		$this->app['config']->set('remote.connections.'.$connection, $credentials);
	}

	/**
	 * Set the active connections
	 *
	 * @param string|array $connections
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
	 */
	public function setConnection($connection)
	{
		if (!$this->isValidConnection($connection)) {
			return;
		}

		// Set the connection
		$this->connection = $connection;
		$this->app['config']->set('remote.default', $connection);

		// Update events
		$this->app['rocketeer.tasks']->registerConfiguredEvents();
	}

	/**
	 * Flush active connection(s)
	 *
	 * @return void
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
	 * @return array
	 */
	public function getCredentials()
	{
		$credentials = $this->app['rocketeer.server']->getValue('credentials');
		if (!$credentials) {
			$credentials = $this->app['rocketeer.rocketeer']->getOption('scm');
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
		exec($this->app['rocketeer.scm']->currentBranch(), $fallback);
		$fallback = trim($fallback[0]) ?: 'master';
		$branch   = $this->app['rocketeer.rocketeer']->getOption('scm.branch') ?: $fallback;

		return $branch;
	}
}
