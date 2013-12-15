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
	protected $container;

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
	 * Export the configuration files and replace stubs in them
	 *
	 * @param array $values
	 *
	 * @return void
	 */
	public function exportConfiguration(array $values = array())
	{
		$source      = __DIR__.'/../config';
		$destination = $this->app['path.base'].'/rocketeer';

		// Unzip configuration files
		$this->app['files']->copyDirectory($source, $destination);

		// Replace stub values in files
		$files = $this->app['files']->files($destination);
		foreach ($files as $file) {
			foreach ($values as $name => $value) {
				$contents = str_replace('{' .$name. '}', $value, file_get_contents($file));
				$this->app['files']->put($file, $contents);
			}
		}

		// Change repository in use
		$this->app['rocketeer.server']->setRepository($values['application_name']);

		return $destination;
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

		// Get the root directory
		$root = __DIR__.'/../..';

		// Replace phar components to allow file checks
		if (strpos(__DIR__, 'phar://') !== false) {
			$root = str_replace('phar://', null, __DIR__);
			$root = preg_replace('#/rocketeer(\.phar)?/src.+#', null, $root);
		}

		// Compute path
		$root = stream_resolve_include_path($root) ?: $root;

		$this->app->instance('path.base', $root);
	}

	/**
	 * Bind paths to the configuration files
	 *
	 * @return void
	 */
	protected function bindConfiguration()
	{
		$path  = $this->app['path.base'] ? $this->app['path.base'].'/' : '';
		$paths = array(
			'config' => 'rocketeer',
			'tasks'  => 'rocketeer/tasks',
		);

		foreach ($paths as $key => $file) {
			$file     = str_replace('tasks', 'tasks.php', $file);
			$filename = $path.$file;

			// Use configuration in current folder if none found
			$realpath = realpath('.').'/'.$file;
			if (!file_exists($filename) and file_exists($realpath)) {
				$filename = $realpath;
			}

			$this->app->instance('path.rocketeer.'.$key, $filename);
		}
	}
}
