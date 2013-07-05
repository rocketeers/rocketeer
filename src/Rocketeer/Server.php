<?php
namespace Rocketeer;

use Illuminate\Container\Container;

/**
 * Provids and persists informations about the remote server
 */
class Server
{

	/**
	 * The IoC Container
	 *
	 * @var Container
	 */
	protected $app;

	/**
	 * The path to the storage file
	 *
	 * @var string
	 */
	protected $repository;

	/**
	 * Build a new ReleasesManager
	 *
	 * @param Container  $app
	 * @param string     $storage Path to the storage folder
	 */
	public function __construct(Container $app, $storage = null)
	{
		$storage = $storage ?: $app['path.storage'];

		$this->app        = $app;
		$this->repository = $storage.'/meta/deployments.json';
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////// REMOTE VARIABLES ///////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the Apache username and group
	 *
	 * @return string
	 */
	public function getApacheCredentials()
	{
		$bash = $this->app['rocketeer.bash'];

		return $this->getValue('apache', function($server) use ($bash) {

			// Get Apache envvars
			$apache = $bash->runRemoteCommands('find /etc -maxdepth 1 -name "apache*" -type d', true);
			print $apache;
			$apache = end($apache);
			print $apache;
			$envvars  = $bash->run("find $apache -name 'envvars'");
			print $envvars;
			if (!$envvars) $envvars = $bash->run("find /usr/sbin -name 'envvars'");
			print $envvars;
			$username = $bash->run('cat '.$envvars. ' | grep APACHE_RUN_USER');
			$group    = $bash->run('cat '.$envvars. ' | grep APACHE_RUN_GROUP');
			print $username;
			// Get username and group
			$username = array_get(explode('=', $username), '1', 'www-data');
			$group    = array_get(explode('=', $group), '1', 'www-data');

			// Save
			$credentials = compact('username', 'group');
			$server->setValue('apache', $credentials);

			return $credentials;
		});
	}

	/**
	 * Get the directory separators
	 *
	 * @return string
	 */
	public function getSeparator()
	{
		$bash = $this->app['rocketeer.bash'];

		return $this->getValue('directory_separator', function($server) use ($bash) {
			$separator = $bash->runRemoteCommands('php -r "echo DIRECTORY_SEPARATOR;"');
			$server->setValue('directory_separator', $separator);

			return $separator;
		});
	}

	/**
	 * Get the remote line endings
	 *
	 * @return string
	 */
	public function getLineEndings()
	{
		$bash = $this->app['rocketeer.bash'];

		return $this->getValue('line_endings', function($server) use ($bash) {
			$endings = $bash->runRemoteCommands('php -r "echo PHP_EOL;"');
			$server->setValue('line_endings', $endings);

			return $endings;
		});
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// KEYSTORE ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get a value from the deployments file
	 *
	 * @param  string         $key
	 * @param  \Closure|string $fallback
	 *
	 * @return mixed
	 */
	public function getValue($key, $fallback = null)
	{
		$value = array_get($this->getRepository(), $key, null);

		// Get fallback value
		if (is_null($value)) {
			return is_callable($fallback) ? $fallback($this) : $fallback;
		}

		return $value;
	}

	/**
	 * Set a value from the deployments file
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function setValue($key, $value)
	{
		$deployments = $this->getRepository();
		array_set($deployments, $key, $value);

		$this->updateRepository($deployments);
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////// REPOSITORY FILE /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Update the deployments file
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	protected function updateRepository($data)
	{
		$this->app['files']->put($this->repository, json_encode($data));
	}

	/**
	 * Get the contents of the deployments file
	 *
	 * @return array
	 */
	protected function getRepository()
	{
		// Cancel if the file doesn't exist
		if (!$this->app['files']->exists($this->repository)) {
			return array();
		}

		// Get and parse file
		$deployments = $this->app['files']->get($this->repository);
		$deployments = json_decode($deployments, true);

		return $deployments;
	}

	/**
	 * Deletes the deployments file
	 *
	 * @return boolean
	 */
	public function deleteRepository()
	{
		return $this->app['files']->delete($this->repository);
	}

}
