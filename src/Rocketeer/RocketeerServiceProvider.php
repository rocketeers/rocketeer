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

use Illuminate\Config\FileLoader;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Log\Writer;
use Illuminate\Remote\RemoteManager;
use Illuminate\Support\ServiceProvider;
use Monolog\Logger;

// Define DS
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

/**
 * Bind the various Rocketeer classes to a Container
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class RocketeerServiceProvider extends ServiceProvider
{
	/**
	 * The commands to register
	 *
	 * @var array
	 */
	protected $commands;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// ...
	}

	/**
	 * Bind classes and commands
	 *
	 * @return void
	 */
	public function boot()
	{
		// Register classes and commands
		$this->app = static::make($this->app);
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('rocketeer');
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////// CLASS BINDINGS /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Make a Rocketeer container
	 *
	 * @param Container $app
	 *
	 * @return Container
	 */
	public static function make($app = null)
	{
		if (!$app) {
			$app = new Container;
		}

		$serviceProvider = new static($app);

		// Bind core paths and classes
		$app = $serviceProvider->bindPaths($app);
		$app = $serviceProvider->bindCoreClasses($app);

		// Bind Rocketeer's classes
		$app = $serviceProvider->bindClasses($app);
		$app = $serviceProvider->bindScm($app);

		// Load the user's events and tasks
		$app = $serviceProvider->loadFileOrFolder($app, 'tasks');
		$app = $serviceProvider->loadFileOrFolder($app, 'events');

		// Bind commands
		$app = $serviceProvider->bindCommands($app);

		return $app;
	}

	/**
	 * Bind the Rocketeer paths
	 *
	 * @param Container $app
	 *
	 * @return Container
	 */
	public function bindPaths(Container $app)
	{
		$app->bind('rocketeer.igniter', function ($app) {
			return new Igniter($app);
		});

		// Bind paths
		$app['rocketeer.igniter']->bindPaths();

		return $app;
	}

	/**
	 * Bind the core classes
	 *
	 * @param  Container $app
	 *
	 * @return Container
	 */
	public function bindCoreClasses(Container $app)
	{
		$app->bindIf('files', 'Illuminate\Filesystem\Filesystem');

		$app->bindIf('request', function () {
			return Request::createFromGlobals();
		}, true);

		$app->bindIf('config', function ($app) {
			$fileloader = new FileLoader($app['files'], __DIR__.'/../config');

			return new Repository($fileloader, 'config');
		}, true);

		$app->bindIf('remote', function ($app) {
			return new RemoteManager($app);
		}, true);

		$app->bindIf('events', function ($app) {
			return new Dispatcher($app);
		}, true);

		$app->bindIf('log', function () {
			return new Writer(new Logger('rocketeer'));
		}, true);

		// Register factory and custom configurations
		$app = $this->registerConfig($app);

		return $app;
	}

	/**
	 * Bind the Rocketeer classes to the Container
	 *
	 * @param  Container $app
	 *
	 * @return Container
	 */
	public function bindClasses(Container $app)
	{
		$app->singleton('rocketeer.rocketeer', function ($app) {
			return new Rocketeer($app);
		});

		$app->bind('rocketeer.releases', function ($app) {
			return new ReleasesManager($app);
		});

		$app->bind('rocketeer.server', function ($app) {
			$filename = $app['rocketeer.rocketeer']->getApplicationName();
			$filename = $filename === '{application_name}' ? 'deployments' : $filename;

			return new Server($app, $filename);
		});

		$app->bind('rocketeer.bash', function ($app) {
			return new Bash($app);
		});

		$app->singleton('rocketeer.queue', function ($app) {
			return new TasksQueue($app);
		});

		$app->singleton('rocketeer.tasks', function ($app) {
			return new TasksHandler($app);
		});

		$app->singleton('rocketeer.logs', function ($app) {
			return new LogsHandler($app);
		});

		$app->singleton('rocketeer.console', function () {
			return new Console\Console('Rocketeer', Rocketeer::VERSION);
		});

		$app['rocketeer.console']->setLaravel($app);
		$app['rocketeer.rocketeer']->syncConnectionCredentials();

		return $app;
	}

	/**
	 * Bind the SCM instance
	 *
	 * @param  Container $app
	 *
	 * @return Container
	 */
	public function bindScm(Container $app)
	{
		// Currently only one
		$scm = $this->app['rocketeer.rocketeer']->getOption('scm.scm');
		$scm = 'Rocketeer\Scm\\'.ucfirst($scm);

		$app->bind('rocketeer.scm', function ($app) use ($scm) {
			return new $scm($app);
		});

		return $app;
	}

	/**
	 * Bind the commands to the Container
	 *
	 * @param  Container $app
	 *
	 * @return Container
	 */
	public function bindCommands(Container $app)
	{
		// Base commands
		$tasks = array(
			''         => 'Rocketeer',
			'check'    => 'Check',
			'cleanup'  => 'Cleanup',
			'current'  => 'CurrentRelease',
			'deploy'   => 'Deploy',
			'flush'    => 'Flush',
			'ignite'   => 'Ignite',
			'rollback' => 'Rollback',
			'setup'    => 'Setup',
			'teardown' => 'Teardown',
			'test'     => 'Test',
			'update'   => 'Update',
		);

		// Add User commands
		$userTasks = (array) $this->app['config']->get('rocketeer::hooks.custom');
		$tasks     = array_merge($tasks, $userTasks);

		// Bind the commands
		foreach ($tasks as $slug => $task) {

			// Check if we have an actual command to use
			$commandClass = 'Rocketeer\Commands\\'.$task.'Command';
			$fakeCommand  = !class_exists($commandClass);

			// Build command slug
			$taskInstance = $this->app['rocketeer.tasks']->buildTaskFromClass($task);
			if (is_numeric($slug)) {
				$slug = $taskInstance->getSlug();
			}

			// Bind Task to container
			$handle = 'rocketeer.tasks.'.$slug;
			$this->app->bind($handle, function () use ($taskInstance) {
				return $taskInstance;
			});

			// Add command to array
			$command = trim('deploy.'.$slug, '.');
			$this->commands[] = $command;

			// Look for an existing command
			if (!$fakeCommand) {
				$this->app->singleton($command, function () use ($commandClass) {
					return new $commandClass;
				});

			// Else create a fake one
			} else {
				$this->app->bind($command, function () use ($taskInstance, $slug) {
					return new Commands\BaseTaskCommand($taskInstance, $slug);
				});
			}

		}

		// Add commands to Artisan
		foreach ($this->commands as $command) {
			$app['rocketeer.console']->add($app[$command]);
			if (isset($app['events'])) {
				$this->commands($command);
			}
		}

		return $app;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Register factory and custom configurations
	 *
	 * @param  Container $app
	 *
	 * @return Container
	 */
	protected function registerConfig(Container $app)
	{
		// Register config file
		$app['config']->package('anahkiasen/rocketeer', __DIR__.'/../config');
		$app['config']->getLoader();

		// Register custom config
		$custom = $app['path.rocketeer.config'];
		if (file_exists($custom)) {
			$app['config']->afterLoading('rocketeer', function ($me, $group, $items) use ($custom) {
				$customItems = $custom.'/'.$group.'.php';
				if (!file_exists($customItems)) {
					return $items;
				}

				$customItems = include $customItems;

				return array_replace($items, $customItems);
			});
		}

		return $app;
	}

	/**
	 * Load a file or its contents if a folder
	 *
	 * @param Container $app
	 * @param string    $handle
	 *
	 * @return Container
	 */
	protected function loadFileOrFolder(Container $app, $handle)
	{
		// Bind ourselves into the facade to avoid automatic resolution
		Facades\Rocketeer::setFacadeApplication($app);

		// If we have one unified tasks file, include it
		$file = $app['path.rocketeer.'.$handle];
		if (!is_dir($file) and file_exists($file)) {
			include $file;
		}

		// Else include its contents
		elseif (is_dir($file)) {
			$folder = glob($file.'/*.php');
			foreach ($folder as $file) {
				include $file;
			}
		}

		return $app;
	}
}
