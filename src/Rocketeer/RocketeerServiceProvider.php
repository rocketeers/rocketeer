<?php
namespace Rocketeer;

use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

/**
 * Bind the various Rocketeer classes to Laravel
 */
class RocketeerServiceProvider extends ServiceProvider
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// Register config file
		$this->app['config']->package('anahkiasen/rocketeer', __DIR__.'/../config');

		// Register classes and commands
		$this->app = static::bindClasses($this->app);
		$this->app = static::bindCommands($this->app);

		$this->commands('deploy', 'deploy.check', 'deploy.setup', 'deploy.deploy', 'deploy.cleanup', 'deploy.test', 'deploy.rollback', 'deploy.teardown', 'deploy.update', 'deploy.current');

		$userCommands = $this->bindUserCommands();
		foreach ($userCommands as $command) {
			$this->commands($command);
		}
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
	 * Bind the Rocketeer classes to the Container
	 *
	 * @param  Container $app
	 *
	 * @return Container
	 */
	public static function bindClasses(Container $app)
	{
		$app->bind('rocketeer.rocketeer', function($app) {
			return new Rocketeer($app);
		});

		$app->bind('rocketeer.releases', function($app) {
			return new ReleasesManager($app);
		});

		$app->bind('rocketeer.deployments', function($app) {
			return new DeploymentsManager($app['files'], $app['path.storage']);
		});

		$app->singleton('rocketeer.tasks', function($app) {
			return new TasksQueue($app);
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
	public static function bindCommands(Container $app)
	{
		$commands = array(
			'',
			'check',
			'setup',
			'deploy',
			'update',
			'rollback',
			'cleanup',
			'current',
			'test',
			'teardown',
		);

		foreach ($commands as $command) {
			// Get class and command
			$class = 'Rocketeer\Commands\Deploy'.ucfirst($command).'Command';
			$slug  = trim('deploy.'.$command, '.');

			// Bind class
			$app->bind($slug, function($app) use ($class) {
				return new $class($app);
			});
		}

		return $app;
	}

	/**
	 * Register the User-defined commands with Laravel
	 *
	 * @return array
	 */
	public function bindUserCommands()
	{
		// Custom tasks
		$tasks = (array) $this->app['config']->get('rocketeer::tasks.custom');
		foreach ($tasks as &$task) {
			$task    = $this->app['rocketeer.tasks']->buildTask($task);
			$command = 'deploy.'.$task->getSlug();

			$this->app->bind($command, function($app) use ($task) {
				return new Commands\DeployCustomCommand($task);
			});

			$task = $command;
		}

		return $tasks;
	}

}
