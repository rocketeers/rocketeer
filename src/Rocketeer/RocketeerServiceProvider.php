<?php
namespace Rocketeer;

use Illuminate\Config\FileLoader;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Remote\RemoteManager;
use Illuminate\Support\ServiceProvider;

// Define DS
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

/**
 * Bind the various Rocketeer classes to Laravel
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
	 * @return Container
	 */
	public static function make($app = null)
	{
		if (!$app) {
			$app = new Container;
		}

		$serviceProvider = new static($app);

		// Bind classes
		$app = $serviceProvider->bindPaths($app);
		$app = $serviceProvider->bindCoreClasses($app);
		$app = $serviceProvider->bindClasses($app);
		$app = $serviceProvider->bindScm($app);
		$app = $serviceProvider->bindCommands($app);
		$app = $serviceProvider->bindTasks($app);

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
		// Register core paths
		if (!$app->bound('path.base')) {
			$root = __DIR__.'/../../..';

			// Replace phar components to allow file checks
			if (strpos(__DIR__, 'phar://') !== false) {
				$root = str_replace('phar://', null, __DIR__);
				$root = preg_replace('#/rocketeer(\.phar)?/src.+#', null, $root);
			}

			$app->instance('path.base', $root);
		}

		$path = $app['path.base'] ? $app['path.base'].'/' : '';

		// Bind custom paths
		$app->instance('path.rocketeer.config', stream_resolve_include_path($path.'rocketeer.php'));
		$app->instance('path.rocketeer.tasks',  stream_resolve_include_path($path.'tasks.php'));

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

		$app->bindIf('request', function ($app) {
			return Request::createFromGlobals();
		}, true);

		$app->bindIf('config', function ($app) {
			$fileloader = new FileLoader($app['files'], __DIR__.'/../config');

			return new Repository($fileloader, 'config');
		}, true);

		$app->bindIf('remote', function ($app) {
			return new RemoteManager($app);
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
			return new Server($app);
		});

		$app->bind('rocketeer.bash', function ($app) {
			return new Bash($app);
		});

		$app->singleton('rocketeer.tasks', function ($app) {
			return new TasksQueue($app);
		});

		$app->singleton('rocketeer.console', function ($app) {
			return new Console\Console('Rocketeer', Rocketeer::VERSION);
		});

		$app['rocketeer.console']->setLaravel($app);

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
			''         => '',
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
		$userTasks = (array) $this->app['config']->get('rocketeer::tasks.custom');
		$tasks     = array_merge($tasks, $userTasks);

		// Bind the commands
		foreach ($tasks as $slug => $task) {

			// Check if we have an actual command to use
			$commandClass = 'Rocketeer\Commands\Deploy'.$task.'Command';
			$fakeCommand  = !class_exists($commandClass);

			// Build command slug
			if ($fakeCommand) {
				$taskInstance = $this->app['rocketeer.tasks']->buildTask($task);
				if (is_numeric($slug)) {
					$slug = $taskInstance->getSlug();
				}
			}

			// Add command to array
			$command = trim('deploy.'.$slug, '.');
			$this->commands[] = $command;

			// Look for an existing command
			if (!$fakeCommand) {
				$this->app->bind($command, function ($app) use ($commandClass) {
					return new $commandClass;
				});

			// Else create a fake one
			} else {
				$this->app->bind($command, function ($app) use ($taskInstance, $slug) {
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

		// Register custom config
		$custom = $app['path.rocketeer.config'];
		if (file_exists($custom)) {
			$app['config']->afterLoading('rocketeer', function ($me, $group, $items) use ($custom) {
				$custom = include $custom;
				return array_replace_recursive($items, $custom);
			});
		}

		return $app;
	}

	/**
	 * Bind custom tasks
	 *
	 * @param Container $app
	 *
	 * @return Container
	 */
	protected function bindTasks(Container $app)
	{
		Facades\Rocketeer::setFacadeApplication($app);

		// Register custom tasks
		$tasks = $app['path.rocketeer.tasks'];
		if (file_exists($tasks)) {
			include $tasks;
		}

		return $app;
	}
}
