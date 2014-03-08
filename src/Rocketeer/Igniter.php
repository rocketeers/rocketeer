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

use Illuminate\Container\Container;

/**
 * Finds configurations and paths
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Igniter
{
	/**
	 * The Container
	 *
	 * @var Container
	 */
	protected $app;

	/**
	 * Build a new Igniter
	 *
	 * @param Container $app
	 */
	public function __construct(Container $app)
	{
		$this->app = $app;
	}

	/**
	 * Bind paths to the container
	 *
	 * @return void
	 */
	public function bindPaths()
	{
		$this->bindBase();
		$this->bindConfiguration();
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// IGNITION ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the path to the configuration folder
	 *
	 * @return string
	 */
	public function getConfigurationPath()
	{
		// Return path to Laravel configuration
		if ($this->app->bound('path')) {
			$laravel = $this->app['path'].'/config/packages/anahkiasen/rocketeer';
			if (file_exists($laravel)) {
				return $laravel;
			}
		}

		return $this->app['path.rocketeer.config'];
	}

	/**
	 * Export the configuration files
	 *
	 * @return void
	 */
	public function exportConfiguration()
	{
		$source      = __DIR__.'/../config';
		$destination = $this->getConfigurationPath();

		// Unzip configuration files
		$this->app['files']->copyDirectory($source, $destination);

		return $destination;
	}

	/**
	 * Replace placeholders in configuration
	 *
	 * @param string $folder
	 * @param array  $values
	 *
	 * @return void
	 */
	public function updateConfiguration($folder, array $values = array())
	{
		// Replace stub values in files
		$files = $this->app['files']->files($folder);
		foreach ($files as $file) {
			foreach ($values as $name => $value) {
				$contents = str_replace('{' .$name. '}', $value, file_get_contents($file));
				$this->app['files']->put($file, $contents);
			}
		}

		// Change repository in use
		$application = array_get($values, 'application_name');
		$this->app['rocketeer.server']->setRepository($application);
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// PATHS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Bind the base path to the Container
	 *
	 * @return void
	 */
	protected function bindBase()
	{
		if ($this->app->bound('path.base')) {
			return;
		}

		$this->app->instance('path.base', getcwd());
	}

	/**
	 * Bind paths to the configuration files
	 *
	 * @return void
	 */
	protected function bindConfiguration()
	{
		$path = $this->getBasePath();
		$logs = $this->getStoragePath();

		// Prepare the paths to bind
		$paths = array(
			'config' => '.rocketeer',
			'events' => '.rocketeer/events',
			'tasks'  => '.rocketeer/tasks',
			'logs'   => $logs.'/logs',
		);

		foreach ($paths as $key => $file) {
			$filename = $path.$file;

			// Check whether we provided a file or folder
			if (!is_dir($filename) and file_exists($filename.'.php')) {
				$filename .= '.php';
			}

			// Use configuration in current folder if none found
			$realpath = realpath('.').'/'.$file;
			if (!file_exists($filename) and file_exists($realpath)) {
				$filename = $realpath;
			}

			$this->app->instance('path.rocketeer.'.$key, $filename);
		}
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the base path
	 *
	 * @return string
	 */
	protected function getBasePath()
	{
		$base = $this->app['path.base'] ? $this->app['path.base'].'/' : '';
		$base = $this->unifySlashes($base);

		return $base;
	}

	/**
	 * Get path to the storage folder
	 *
	 * @return string
	 */
	protected function getStoragePath()
	{
		// If no path is bound, default to the Rocketeer folder
		if (!$this->app->bound('path.storage')) {
			return '.rocketeer';
		}

		// Unify slashes
		$storage = $this->app['path.storage'];
		$storage = $this->unifySlashes($storage);
		$storage = str_replace($this->getBasePath(), null, $storage);

		return $storage;
	}

	/**
	 * Unify the slashes to the UNIX mode (forward slashes)
	 *
	 * @param  string $path
	 *
	 * @return string
	 */
	protected function unifySlashes($path)
	{
		return str_replace('\\', '/', $path);
	}
}
