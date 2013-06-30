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
		// Register config file
		$this->app['config']->package('anahkiasen/rocketeer', __DIR__.'/../config');
	}

	/**
	 * Bind classes and commands
	 *
	 * @return void
	 */
	public function boot()
	{
		// Register classes and commands
		$this->app = $this->bindClasses($this->app);
		$this->app = $this->bindCommands($this->app);

		// Add commands to Artisan
		foreach ($this->commands as $command) {
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
	public function bindClasses(Container $app)
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
	public function bindCommands(Container $app)
	{
		// Base commands
		$tasks = array(
			''         => '',
			'check'    => 'Check',
			'setup'    => 'Setup',
			'deploy'   => 'Deploy',
			'update'   => 'Update',
			'rollback' => 'Rollback',
			'cleanup'  => 'Cleanup',
			'current'  => 'CurrentRelease',
			'test'     => 'Test',
			'teardown' => 'Teardown',
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
				if (is_numeric($slug)) $slug = $taskInstance->getSlug();
			}

			// Add command to array
			$command = trim('deploy.'.$slug, '.');
			$this->commands[] = $command;

			// Look for an existing command
			if (!$fakeCommand) {
				$this->app->bind($command, function($app) use ($commandClass) {
					return new $commandClass;
				});
			}

			// Else create a fake one
			else {
				$this->app->bind($command, function($app) use ($taskInstance, $slug) {
					return new Commands\BaseTaskCommand($taskInstance, $slug);
				});
			}

		}

		return $app;
	}

}
