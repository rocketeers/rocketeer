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
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Log\Writer;
use Illuminate\Remote\RemoteManager;
use Illuminate\Support\ServiceProvider;
use Monolog\Logger;
use Rocketeer\Console\Commands\BaseTaskCommand;
use Rocketeer\Services\ConnectionsHandler;
use Rocketeer\Services\History\History;
use Rocketeer\Services\History\LogsHandler;
use Rocketeer\Services\ReleasesManager;
use Rocketeer\Services\Storages\LocalStorage;
use Rocketeer\Services\TasksHandler;
use Rocketeer\Services\TasksQueue;

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
		$this->bindPaths();
		$this->bindCoreClasses();

		// Bind Rocketeer's classes
		$this->bindClasses();
		$this->bindStrategies();

		// Load the user's events and tasks
		$this->loadFileOrFolder('tasks');
		$this->loadFileOrFolder('events');

		// Bind commands
		$this->bindCommands();
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
	 * Bind the Rocketeer paths
	 */
	public function bindPaths()
	{
		$this->app->bind('rocketeer.igniter', function ($app) {
			return new Igniter($app);
		});

		// Bind paths
		$this->app['rocketeer.igniter']->bindPaths();
	}

	/**
	 * Bind the core classes
	 */
	public function bindCoreClasses()
	{
		$this->app->bindIf('files', 'Illuminate\Filesystem\Filesystem');

		$this->app->bindIf('request', function () {
			return Request::createFromGlobals();
		}, true);

		$this->app->bindIf('config', function ($app) {
			$fileloader = new FileLoader($app['files'], __DIR__.'/../config');

			return new Repository($fileloader, 'config');
		}, true);

		$this->app->bindIf('remote', function ($app) {
			return new RemoteManager($app);
		}, true);

		$this->app->bindIf('events', function ($app) {
			return new Dispatcher($app);
		}, true);

		$this->app->bindIf('log', function () {
			return new Writer(new Logger('rocketeer'));
		}, true);

		// Register factory and custom configurations
		$this->registerConfig();
	}

	/**
	 * Bind the Rocketeer classes to the Container
	 */
	public function bindClasses()
	{
		$this->app->singleton('rocketeer.rocketeer', function ($app) {
			return new Rocketeer($app);
		});

		$this->app->singleton('rocketeer.connections', function ($app) {
			return new ConnectionsHandler($app);
		});

		$this->app->singleton('rocketeer.releases', function ($app) {
			return new ReleasesManager($app);
		});

		$this->app->bind('rocketeer.server', function ($app) {
			$filename = $app['rocketeer.rocketeer']->getApplicationName();
			$filename = $filename === '{application_name}' ? 'deployments' : $filename;

			return new LocalStorage($app, $filename);
		});

		$this->app->bind('rocketeer.bash', function ($app) {
			return new Bash($app);
		});

		$this->app->singleton('rocketeer.queue', function ($app) {
			return new TasksQueue($app);
		});

		$this->app->singleton('rocketeer.tasks', function ($app) {
			return new TasksHandler($app);
		});

		$this->app->singleton('rocketeer.history', function () {
			return new History;
		});

		$this->app->singleton('rocketeer.logs', function ($app) {
			return new LogsHandler($app);
		});

		$this->app->singleton('rocketeer.console', function () {
			return new Console\Console('Rocketeer', Rocketeer::VERSION);
		});

		$this->app['rocketeer.console']->setLaravel($this->app);
		$this->app['rocketeer.connections']->syncConnectionCredentials();
	}

	/**
	 * Bind the SCM instance
	 */
	public function bindStrategies()
	{
		// Bind SCM class
		$scm = $this->app['rocketeer.rocketeer']->getOption('scm.scm');
		$scm = 'Rocketeer\Scm\\'.ucfirst($scm);

		$this->app->bind('rocketeer.scm', function ($app) use ($scm) {
			return new $scm($app);
		});

		// Bind strategy
		$this->app->bind('rocketeer.strategy', function ($app) {
			$strategy = $app['rocketeer.rocketeer']->getOption('remote.strategy');
			$strategy = sprintf('Rocketeer\Strategies\%sStrategy', ucfirst($strategy));

			return new $strategy($app);
		});
	}

	/**
	 * Bind the commands to the Container
	 */
	public function bindCommands()
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
			$commandClass = 'Rocketeer\Console\Commands\\'.$task.'Command';
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
			$command          = trim('deploy.'.$slug, '.');
			$this->commands[] = $command;

			// Look for an existing command
			if (!$fakeCommand) {
				$this->app->singleton($command, function () use ($commandClass) {
					return new $commandClass;
				});
				// Else create a fake one
			} else {
				$this->app->bind($command, function () use ($taskInstance, $slug) {
					return new BaseTaskCommand($taskInstance, $slug);
				});
			}
		}

		// Add commands to Artisan
		foreach ($this->commands as $command) {
			$this->app['rocketeer.console']->add($this->app[$command]);
			if (isset($this->app['events'])) {
				$this->commands($command);
			}
		}
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Register factory and custom configurations
	 */
	protected function registerConfig()
	{
		// Register config file
		$this->app['config']->package('anahkiasen/rocketeer', __DIR__.'/../config');
		$this->app['config']->getLoader();

		// Register custom config
		$custom = $this->app['path.rocketeer.config'];
		if (file_exists($custom)) {
			$this->app['config']->afterLoading('rocketeer', function ($me, $group, $items) use ($custom) {
				$customItems = $custom.'/'.$group.'.php';
				if (!file_exists($customItems)) {
					return $items;
				}

				$customItems = include $customItems;

				return array_replace($items, $customItems);
			});
		}
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
		if (!is_dir($file) and file_exists($file)) {
			include $file;
		} // Else include its contents
		elseif (is_dir($file)) {
			$folder = glob($file.'/*.php');
			foreach ($folder as $file) {
				include $file;
			}
		}
	}
}
