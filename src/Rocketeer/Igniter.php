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

use Illuminate\Support\Arr;
use Rocketeer\Traits\HasLocator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Ignites Rocketeer's custom configuration, tasks, events and paths
 * depending on what Rocketeer is used on
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Igniter
{
	use HasLocator;

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

	//////////////////////////////////////////////////////////////////////
	///////////////////////// USER CONFIGURATION /////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Load the custom files (tasks, events, ...)
	 */
	public function loadUserConfiguration()
	{
		$fileLoaders = function () {
			$this->loadFileOrFolder('tasks');
			$this->loadFileOrFolder('events');
		};

		// Defer loading of tasks and events or not
		if (is_a($this->app, 'Illuminate\Foundation\Application')) {
			$this->app->booted($fileLoaders);
		} else {
			$fileLoaders();
		}

		// Merge contextual configurations
		$this->mergeContextualConfigurations();
	}

	/**
	 * Merge the various contextual configurations defined in userland
	 */
	public function mergeContextualConfigurations()
	{
		// Cancel if not ignited yet
		$storage = $this->app['path.rocketeer.config'];
		if (!is_dir($storage) || (!is_dir($storage.'/stages') && !is_dir($storage.'/connections'))) {
			return;
		}

		// Gather custom files
		$finder = new Finder();
		$files  = $finder->in($storage.'/{stages,connections}/*')->notName('config.php')->files();
		$files  = iterator_to_array($files);

		// Bind their contents to the "on" array
		foreach ($files as $file) {
			$contents = include $file->getPathname();
			$handle   = $this->computeHandleFromPath($file);

			$this->config->set($handle, $contents);
		}
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// CONFIGURATION ////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the path to the configuration folder
	 *
	 * @return string
	 */
	public function getConfigurationPath()
	{
		// Return path to Laravel configuration
		if ($this->isInsideLaravel()) {
			return $this->app['path'].'/config/packages/anahkiasen/rocketeer';
		}

		return $this->app['path.rocketeer.config'];
	}

	/**
	 * Export the configuration files
	 *
	 * @return string
	 */
	public function exportConfiguration()
	{
		$source      = __DIR__.'/../config';
		$destination = $this->getConfigurationPath();

		// Unzip configuration files
		$this->files->copyDirectory($source, $destination);

		return $destination;
	}

	/**
	 * Replace placeholders in configuration
	 *
	 * @param string   $folder
	 * @param string[] $values
	 */
	public function updateConfiguration($folder, array $values = array())
	{
		// Replace stub values in files
		$files = $this->files->files($folder);
		foreach ($files as $file) {
			foreach ($values as $name => $value) {
				$contents = str_replace('{'.$name.'}', $value, file_get_contents($file));
				$this->files->put($file, $contents);
			}
		}

		// Change repository in use
		$application = Arr::get($values, 'application_name');
		$this->localStorage->setFile($application);
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// PATHS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the base path
	 *
	 * @return string
	 */
	public function getBasePath()
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
	public function getStoragePath()
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
	 * Bind the base path to the Container
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
	 */
	protected function bindConfiguration()
	{
		// Bind path to the configuration directory
		if ($this->isInsideLaravel()) {
			$path    = $this->app['path'].'/config/packages/anahkiasen/rocketeer';
			$storage = $this->getStoragePath();
		} else {
			$path    = $this->getBasePath().'.rocketeer';
			$storage = $path;
		}

		// Build paths
		$paths = array(
			'config' => $path.'',
			'events' => $path.'/events',
			'tasks'  => $path.'/tasks',
			'logs'   => $storage.'/logs',
		);

		foreach ($paths as $key => $file) {

			// Check whether we provided a file or folder
			if (!is_dir($file) && file_exists($file.'.php')) {
				$file .= '.php';
			}

			// Use configuration in current folder if none found
			$realpath = realpath('.').'/'.basename($file);
			if (!file_exists($file) && file_exists($realpath)) {
				$file = $realpath;
			}

			$this->app->instance('path.rocketeer.'.$key, $file);
		}
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Computes which configuration handle a config file should bind to
	 *
	 * @param SplFileInfo $file
	 *
	 * @return string
	 */
	protected function computeHandleFromPath(SplFileInfo $file)
	{
		// Get realpath
		$handle = $file->getRealpath();

		// Format appropriately
		$handle = str_replace($this->app['path.rocketeer.config'].DS, null, $handle);
		$handle = str_replace('.php', null, $handle);
		$handle = str_replace(DS, '.', $handle);

		return sprintf('rocketeer::on.%s', $handle);
	}

	/**
	 * Load a file or its contents if a folder
	 *
	 * @param string $handle
	 */
	protected function loadFileOrFolder($handle)
	{
		// Bind ourselves into the facade to avoid automatic resolution
		Facades\Rocketeer::setFacadeApplication($this->app);

		// If we have one unified tasks file, include it
		$file = $this->app['path.rocketeer.'.$handle];
		if (!is_dir($file) && file_exists($file)) {
			include $file;
		} // Else include its contents
		elseif (is_dir($file)) {
			$folder = glob($file.'/*.php');
			foreach ($folder as $file) {
				include $file;
			}
		}
	}

	/**
	 * Unify the slashes to the UNIX mode (forward slashes)
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	protected function unifySlashes($path)
	{
		return str_replace('\\', '/', $path);
	}

	/**
	 * Check if this is in Laravel
	 *
	 * @return boolean
	 */
	protected function isInsideLaravel()
	{
		// Return path to Laravel configuration
		if ($this->app->bound('path')) {
			$laravel = $this->app['path'].'/config/packages/anahkiasen/rocketeer';
			if (file_exists($laravel)) {
				return true;
			}
		}

		return false;
	}
}
