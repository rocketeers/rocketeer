<?php
/*
* This file is part of Rocketeer
*
* (c) Maxime Fabre <ehtnam6@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace Rocketeer\Ignition;

use Illuminate\Support\Arr;
use Rocketeer\Facades;
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

		// Load plugins
		var_dump($this->config->get('rocketeer::config'));
		foreach ($this->config->get('rocketeer::plugins') as $plugin) {
			$this->tasks->plugin($plugin);
		}

		// Merge contextual configurations
		$this->mergeContextualConfigurations();
		$this->mergePluginsConfiguration();
	}

	/**
	 * Merge the various contextual configurations defined in userland
	 */
	public function mergeContextualConfigurations()
	{
		// Cancel if not ignited yet
		$configuration = $this->app['path.rocketeer.config'];
		if (!is_dir($configuration) || (!is_dir($configuration.DS.'stages') && !is_dir($configuration.DS.'connections'))) {
			return;
		}

		// Get folders to glob
		$folders = $this->paths->unifyLocalSlashes($configuration.'/{stages,connections}/*');

		// Gather custom files
		$finder = new Finder();
		$files  = $finder->in($folders)->notName('config.php')->files();

		// Bind their contents to the "on" array
		foreach ($files as $file) {
			$contents = include $file->getPathname();
			$handle   = $this->computeHandleFromPath($file);

			$this->config->set($handle, $contents);
		}
	}

	public function mergePluginsConfiguration()
	{
		// Cancel if no plugins
		$configuration = $this->app['path.rocketeer.config'];
		if (!is_dir($configuration) || !is_dir($configuration.DS.'plugins')) {
			return;
		}

		// Get folders to glob
		$folders = $this->paths->unifyLocalSlashes($configuration.'/plugins/*');

		// Gather custom files
		$finder = new Finder();
		$files  = $finder->in($folders)->files();

		// Bind their contents to the "on" array
		foreach ($files as $file) {
			$contents = include $file->getPathname();
			$handle   = basename(dirname($file->getPathname()));
			$handle .= '::'.$file->getBasename('.php');

			$this->config->set($handle, $contents);
		}
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// CONFIGURATION ////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Export the configuration files
	 *
	 * @return string
	 */
	public function exportConfiguration()
	{
		$source      = $this->paths->unifyLocalSlashes(__DIR__.'/../config');
		$source      = realpath($source);
		$destination = $this->paths->getConfigurationPath();

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
			$path    = $this->paths->getConfigurationPath();
			$storage = $this->paths->getStoragePath();
		} else {
			$path = $this->paths->getBasePath().'.rocketeer';

			$storage = $path;
		}

		// Build paths
		$paths = array(
			'config'  => $path.'',
			'events'  => $path.DS.'events',
			'plugins' => $path.DS.'plugins',
			'tasks'   => $path.DS.'tasks',
			'logs'    => $storage.DS.'logs',
		);

		foreach ($paths as $key => $file) {

			// Check whether we provided a file or folder
			if (!is_dir($file) && file_exists($file.'.php')) {
				$file .= '.php';
			}

			// Use configuration in current folder if none found
			$realpath = realpath('.').DS.basename($file);
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
			$folder = glob($file.DS.'*.php');
			foreach ($folder as $file) {
				include $file;
			}
		}
	}
}
