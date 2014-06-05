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

/**
 * Provides and persists informations about the remote server
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
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
	 * The current hash in use
	 *
	 * @var string
	 */
	protected $hash;

	/**
	 * Build a new ReleasesManager
	 *
	 * @param Container  $app
	 * @param string     $filename
	 * @param string     $storage
	 */
	public function __construct(Container $app, $filename = 'deployments', $storage = null)
	{
		$this->app = $app;

		// Create repository and update it if necessary
		$this->setRepository($filename, $storage);
		if ($this->shouldFlush()) {
			$this->deleteRepository();
		}

		// Add salt to current repository
		$this->setValue('hash', $this->getHash());
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// SALTS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the current salt in use
	 *
	 * @return string
	 */
	public function getHash()
	{
		// Return cached hash if any
		if ($this->hash) {
			return $this->hash;
		}

		// Get the contents of the configuration folder
		$salt   = '';
		$folder = $this->app['rocketeer.igniter']->getConfigurationPath();
		$files  = $this->app['files']->glob($folder.'/*.php');

		// Remove custom files and folders
		$handles = array('events', 'tasks');
		foreach ($handles as $handle) {
			$path  = $this->app['path.rocketeer.'.$handle];
			$index = array_search($path, $files);
			if ($index !== false) {
				unset($files[$index]);
			}
		}

		// Compute the salts
		foreach ($files as $file) {
			$file  = $this->app['files']->getRequire($file);
			$salt .= json_encode($file);
		}

		// Cache it
		$this->hash = md5($salt);

		return $this->hash;
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// REPOSITORY ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Flushes the repository if required
	 *
	 * @return void
	 */
	public function shouldFlush()
	{
		$currentHash = $this->getValue('hash');

		return $currentHash and $currentHash !== $this->getHash();
	}

	/**
	 * Change the repository in use
	 *
	 * @param string $filename
	 * @param string $storage
	 */
	public function setRepository($filename, $storage = null)
	{
		// Create personal storage if necessary
		if (!$this->app->bound('path.storage')) {
			$storage = $this->app['rocketeer.rocketeer']->getRocketeerConfigFolder();
			$this->app['files']->makeDirectory($storage, 0755, false, true);
		}

		// Get path to storage
		$storage = $storage ?: $this->app['path.storage'].DS.'meta';

		$this->repository = $storage.DS.$filename.'.json';
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////// REMOTE VARIABLES ///////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the directory separators on the remove server
	 *
	 * @return string
	 */
	public function getSeparator()
	{
		// If manually set by the user, return it
		$user = $this->app['rocketeer.rocketeer']->getOption('remote.variables.directory_separator');
		if ($user) {
			return $user;
		}

		$bash = $this->app['rocketeer.bash'];
		return $this->getValue('directory_separator', function ($server) use ($bash) {
			$separator = $bash->runLast('php -r "echo DIRECTORY_SEPARATOR;"');

			// Throw an Exception if we receive invalid output
			if (strlen($separator) > 1) {
				throw new Exception(
					'An error occured while fetching the directory separators used on the server.'.PHP_EOL.
					'Output received was : '.$separator
				);
			}

			// Cache separator
			$server->setValue('directory_separator', $separator);

			return $separator;
		});
	}

	/**
	 * Get the remote line endings on the remove server
	 *
	 * @return string
	 */
	public function getLineEndings()
	{
		// If manually set by the user, return it
		$user = $this->app['rocketeer.rocketeer']->getOption('remote.variables.line_endings');
		if ($user) {
			return $user;
		}

		$bash = $this->app['rocketeer.bash'];
		return $this->getValue('line_endings', function ($server) use ($bash) {
			$endings = $bash->runRaw('php -r "echo PHP_EOL;"');
			$server->setValue('line_endings', $endings);

			return $endings;
		});
	}

	/**
	 * Check if the current project uses Composer
	 *
	 * @return boolean
	 */
	public function usesComposer()
	{
		$path = $this->app['path.base'].DIRECTORY_SEPARATOR.'composer.json';

		return $this->app['files']->exists($path);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// KEYSTORE ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get a value from the repository file
	 *
	 * @param  string          $key
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
	 * Set a value from the repository file
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return array
	 */
	public function setValue($key, $value)
	{
		$repository = $this->getRepository();
		array_set($repository, $key, $value);

		return $this->updateRepository($repository);
	}

	/**
	 * Forget a value from the repository file
	 *
	 * @param  string $key
	 *
	 * @return array
	 */
	public function forgetValue($key)
	{
		$repository = $this->getRepository();
		array_forget($repository, $key);

		return $this->updateRepository($repository);
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////// REPOSITORY FILE /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Replace the contents of the deployments file
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function updateRepository($data)
	{
		// Yup. Don't look at me like that.
		@$this->app['files']->put($this->repository, json_encode($data));

		return $data;
	}

	/**
	 * Get the contents of the deployments file
	 *
	 * @return array
	 */
	public function getRepository()
	{
		// Cancel if the file doesn't exist
		if (!$this->app['files']->exists($this->repository)) {
			return array();
		}

		// Get and parse file
		$repository = $this->app['files']->get($this->repository);
		$repository = json_decode($repository, true);

		return $repository;
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
