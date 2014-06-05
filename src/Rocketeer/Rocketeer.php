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
 * Handles interaction between the User provided informations
 * and the various Rocketeer components
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Rocketeer
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
	 * The Rocketeer version
	 *
	 * @var string
	 */
	const VERSION = '1.2.2';

	/**
	 * Build a new ReleasesManager
	 *
	 * @param Container $app
	 */
	public function __construct(Container $app)
	{
		$this->app = $app;
	}

	/**
	 * Get an option from Rocketeer's config file
	 *
	 * @param  string $option
	 *
	 * @return mixed
	 */
	public function getOption($option)
	{
		$original = $this->app['config']->get('rocketeer::'.$option);

		if ($contextual = $this->getContextualOption($option, 'stages', $original)) {
			return $contextual;
		}

		if ($contextual = $this->getContextualOption($option, 'connections', $original)) {
			return $contextual;
		}

		return $original;
	}

	/**
	 * Get a contextual option
	 *
	 * @param  string       $option
	 * @param  string       $type     [stage,connection]
	 * @param  string|array $original
	 *
	 * @return mixed
	 */
	protected function getContextualOption($option, $type, $original = null)
	{
		// Switch context
		switch ($type) {
			case 'stages':
				$contextual = sprintf('rocketeer::on.stages.%s.%s', $this->stage, $option);
				break;

			case 'connections':
				$contextual = sprintf('rocketeer::on.connections.%s.%s', $this->getConnection(), $option);
				break;

			default:
				$contextual = sprintf('rocketeer::%s', $option);
				break;
		}

		// Merge with defaults
		$value = $this->app['config']->get($contextual);
		if (is_array($value) and $original) {
			$value = array_replace($original, $value);
		}

		return $value;
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
		return $this->getOption('stages.stages');
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
	 * @return array
	 */
	public function getAvailableConnections()
	{
		$connections = $this->app['rocketeer.server']->getValue('connections');

		// Fetch from config file
		if (!$connections) {
			$connections = $this->app['config']->get('rocketeer::connections');
		}

		// Fetch from remote file
		if (!$connections or array_get($connections, 'production.host') == '{host}') {
			$connections = $this->app['config']->get('remote.connections');
		}

		return $connections;
	}

	/**
	 * Check if a connection has credentials related to it
	 *
	 * @param  string  $connection
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
		$instance = $this;
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

		$connection = array_get($this->getConnections(), 0);
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

		return array_get($this->getAvailableConnections(), $connection, array());
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
	 * Set the curent connection
	 *
	 * @param string $connection
	 */
	public function setConnection($connection)
	{
		if ($this->isValidConnection($connection)) {
			$this->connection = $connection;
			$this->app['config']->set('remote.default', $connection);
		}
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

	/**
	 * Get the name of the application to deploy
	 *
	 * @return string
	 */
	public function getApplicationName()
	{
		return $this->app['config']->get('rocketeer::application_name');
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
			$credentials = $this->getOption('scm');
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
	 * @param  string $username
	 * @param  string $password
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
			$credentials  = $password ? $username.':'.$password : $username;
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
		$branch   = $this->getOption('scm.branch') ?: $fallback;

		return $branch;
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// PATHS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get a configured path
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function getPath($path)
	{
		return $this->getOption('paths.'.$path);
	}

	/**
	 * Replace patterns in a folder path
	 *
	 * @param  string $path
	 *
	 * @return string
	 */
	public function replacePatterns($path)
	{
		$app = $this->app;

		// Replace folder patterns
		return preg_replace_callback('/\{[a-z\.]+\}/', function ($match) use ($app) {
			$folder = substr($match[0], 1, -1);

			if ($app->bound($folder)) {
				return str_replace($app['path.base'].'/', null, $app->make($folder));
			}

			return false;
		}, $path);
	}

	/**
	 * Get the path to a folder, taking into account application name and stage
	 *
	 * @param  string $folder
	 *
	 * @return string
	 */
	public function getFolder($folder = null)
	{
		$folder = $this->replacePatterns($folder);

		$base = $this->getHomeFolder().'/';
		if ($folder and $this->stage) {
			$base .= $this->stage.'/';
		}
		$folder = str_replace($base, null, $folder);

		return $base.$folder;
	}

	/**
	 * Get the path to the root folder of the application
	 *
	 * @return string
	 */
	public function getHomeFolder()
	{
		$rootDirectory = $this->getOption('remote.root_directory');
		$rootDirectory = Str::finish($rootDirectory, '/');
		$appDirectory  = $this->getOption('remote.app_directory') ?: $this->getApplicationName();

		return $rootDirectory.$appDirectory;
	}

	/**
	 * Get the path to the Rocketeer config folder in the users home
	 *
	 * @return string
	 */
	public function getRocketeerConfigFolder()
	{
		return $this->getUserHomeFolder() . '/.rocketeer';
	}

	/**
	 * Get the path to the users home folder
	 *
	 * @return string
	 */
	public function getUserHomeFolder()
	{
		// Get home folder if available (Unix)
		if (!empty($_SERVER['HOME'])) {
			return $_SERVER['HOME'];

		// Else use the homedrive (Windows)
		} elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
			return $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];

		} else {
			throw new Exception('Cannot determine user home directory.');
		}
	}
}
