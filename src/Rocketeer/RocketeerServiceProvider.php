<?php
namespace Rocketeer;

use Illuminate\Support\ServiceProvider;

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

		// Register commands
		$this->registerClasses();
		$this->registerCommands();
	}

	/**
	 * Register the Rocketeer classes
	 */
	protected function registerClasses()
	{
		$this->app->bind('rocketeer.rocketeer', function($app) {
			return new Rocketeer($app);
		});

		$this->app->bind('rocketeer.releases', function($app) {
			return new ReleasesManager($app);
		});

		$this->app->bind('rocketeer.deployments', function($app) {
			return new DeploymentsManager($app);
		});
	}

	/**
	 * Register the Rocketeer commands
	 */
	protected function registerCommands()
	{
		$this->app->bind('deploy',  function($app) {
			return new Commands\DeployCommand($app);
		});

		$this->app->bind('deploy.setup',  function($app) {
			return new Commands\DeploySetupCommand($app);
		});

		$this->app->bind('deploy.deploy', function($app) {
			return new Commands\DeployDeployCommand($app);
		});

		$this->app->bind('deploy.cleanup', function($app) {
			return new Commands\DeployCleanupCommand($app);
		});

		$this->app->bind('deploy.rollback', function($app) {
			return new Commands\DeployRollbackCommand($app);
		});

		$this->app->bind('deploy.current', function($app) {
			return new Commands\DeployCurrentCommand($app);
		});

		$this->commands('deploy', 'deploy.setup', 'deploy.deploy', 'deploy.cleanup', 'deploy.rollback', 'deploy.current');
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

}
