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
use Rocketeer\Exceptions\TaskCompositionException;
use Rocketeer\Services\ConnectionsHandler;
use Rocketeer\Services\CredentialsGatherer;
use Rocketeer\Services\History\History;
use Rocketeer\Services\History\LogsHandler;
use Rocketeer\Services\ReleasesManager;
use Rocketeer\Services\Storages\LocalStorage;
use Rocketeer\Services\Tasks\TasksBuilder;
use Rocketeer\Services\Tasks\TasksQueue;
use Rocketeer\Services\TasksHandler;

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
		$this->bindThirdPartyServices();

		// Bind Rocketeer's classes
		$this->bindCoreClasses();
		$this->bindConsoleClasses();
		$this->bindStrategies();

		// Load the user's events, tasks, and configurations
		$this->app['rocketeer.igniter']->loadUserConfiguration();

		// Bind commands
		$this->bindCommands();
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return string[]
	 */
	public function provides()
	{
		return ['rocketeer'];
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
	public function bindThirdPartyServices()
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
	public function bindCoreClasses()
	{
		$this->app->singleton('rocketeer.rocketeer', function ($app) {
			return new Rocketeer($app);
		});

		$this->app->singleton('rocketeer.connections', function ($app) {
			return new ConnectionsHandler($app);
		});

		$this->app->singleton('rocketeer.explainer', function ($app) {
			return new QueueExplainer($app);
		});

		$this->app->singleton('rocketeer.releases', function ($app) {
			return new ReleasesManager($app);
		});

		$this->app->bind('rocketeer.storage.local', function ($app) {
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

		$this->app->bind('rocketeer.builder', function ($app) {
			return new TasksBuilder($app);
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
	}

	/**
	 * Bind the CredentialsGatherer and Console application
	 */
	public function bindConsoleClasses()
	{
		$this->app->singleton('rocketeer.credentials', function ($app) {
			return new CredentialsGatherer($app);
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

		// Bind strategies
		$strategies = $this->app['rocketeer.rocketeer']->getOption('strategies');
		foreach ($strategies as $strategy => $implementation) {
			if (!is_string($implementation)) {
				continue;
			}
			
			$concrete = sprintf('Rocketeer\Strategies\%s%sStrategy', $implementation, ucfirst($strategy));
			$this->app->bind('rocketeer.strategies.'.$strategy, function ($app) use ($strategy, $concrete) {
				return new $concrete($app);
			});
		}
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

			try {
				$taskInstance = $this->app['rocketeer.builder']->buildTaskFromClass($task);
			} catch (TaskCompositionException $exception) {
				$taskInstance = null;
			}

			// Bind Task to container
			$handle = 'rocketeer.tasks.'.$slug;
			$this->app->bind($handle, function () use ($taskInstance) {
				return $taskInstance;
			});

			// Add command to array
			$command          = trim('deploy.'.$slug, '.');
			$this->commands[] = $command;

			// Register command with the container
			$this->app->singleton($command, function () use ($fakeCommand, $commandClass, $taskInstance, $slug) {
				return !$fakeCommand ? new $commandClass($taskInstance) : new BaseTaskCommand($taskInstance, $slug);
			});
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
		$set = $this->app['path.rocketeer.config'];
		if (!file_exists($set)) {
			return;
		}

		$this->app['config']->afterLoading('rocketeer', function ($me, $group, $items) use ($set) {
			$customItems = $set.'/'.$group.'.php';
			if (!file_exists($customItems)) {
				return $items;
			}

			$customItems = include $customItems;

			return array_replace($items, $customItems);
		});
	}
}
