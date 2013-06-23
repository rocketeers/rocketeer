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

		$this->commands('deploy', 'deploy.setup', 'deploy.deploy', 'deploy.cleanup', 'deploy.rollback', 'deploy.teardown', 'deploy.current');
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
			return new Rocketeer($app['config']);
		});

		$app->bind('rocketeer.releases', function($app) {
			return new ReleasesManager($app);
		});

		$app->bind('rocketeer.deployments', function($app) {
			return new DeploymentsManager($app['files'], $app['path.storage']);
		});

		$app->bind('rocketeer.tasks', function($app) {
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
		$app->bind('deploy',  function($app) {
			return new Commands\DeployCommand($app);
		});

		$app->bind('deploy.setup',  function($app) {
			return new Commands\DeploySetupCommand($app);
		});

		$app->bind('deploy.deploy', function($app) {
			return new Commands\DeployDeployCommand($app);
		});

		$app->bind('deploy.cleanup', function($app) {
			return new Commands\DeployCleanupCommand($app);
		});

		$app->bind('deploy.rollback', function($app) {
			return new Commands\DeployRollbackCommand($app);
		});

		$app->bind('deploy.teardown', function($app) {
			return new Commands\DeployTeardownCommand($app);
		});

		$app->bind('deploy.current', function($app) {
			return new Commands\DeployCurrentCommand($app);
		});

		return $app;
	}

}
