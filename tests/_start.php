<?php
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Rocketeer\RocketeerServiceProvider;

abstract class RocketeerTests extends PHPUnit_Framework_TestCase
{

	/**
	 * The IoC Container
	 *
	 * @var Container
	 */
	protected $app;

	/**
	 * The path to the local fake server
	 *
	 * @var string
	 */
	protected $server;

	/**
	 * Set up the tests
	 */
	public function setUp()
	{
		$this->server = __DIR__.'/server/foobar';

		$this->app = new Container;

		// Get the Mockery instances
		$config = $this->getConfig();
		$remote = $this->getRemote();

		$this->app->singleton('config', function() use ($config) {
			return $config;
		});

		$this->app->singleton('remote', function() use ($remote) {
			return $remote;
		});

		$this->app->singleton('files', function() {
			return new Filesystem;
		});

		// Rocketeer classes ------------------------------------------- /

		$this->app = RocketeerServiceProvider::bindClasses($this->app);

		$this->app->bind('rocketeer.deployments', function($app) {
			return new Rocketeer\DeploymentsManager($app['files'], __DIR__);
		});

		$this->app->singleton('rocketeer.tasks', function($app) {
			return new Rocketeer\TasksQueue($app, $this->getCommand());
		});
	}

	public function tearDown()
	{
		$deployments = array('foo' => 'bar', 'current_release' => 2000000000);
		$this->app['files']->put(__DIR__.'/meta/deployments.json', json_encode($deployments));
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get TasksQueue instance
	 *
	 * @return TasksQueue
	 */
	protected function tasksQueue()
	{
		return $this->app['rocketeer.tasks'];
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// DEPENDENCIES /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Mock the Command class
	 *
	 * @return Mockery
	 */
	protected function getCommand()
	{
		$command = Mockery::mock('Illuminate\Console\Command');
		$command->shouldReceive('info');

		return $command;
	}

	/**
	 * Mock the Config component
	 *
	 * @return Mockery
	 */
	protected function getConfig()
	{
		$config = Mockery::mock('Illuminate\Config\Repository');
		$config->shouldReceive('get')->with('rocketeer::remote.application_name')->andReturn('foobar');
		$config->shouldReceive('get')->with('rocketeer::remote.root_directory')->andReturn(__DIR__.'/server/');
		$config->shouldReceive('get')->with('rocketeer::remote.keep_releases')->andReturn(1);
		$config->shouldReceive('get')->with('rocketeer::git.branch')->andReturn('master');
		$config->shouldReceive('get')->with('rocketeer::connections')->andReturn('production');

		$config->shouldReceive('get')->with('rocketeer::tasks')->andReturn(array(
			'before' => array(
				'deploy' => array('before', 'foobar'),
			),
			'after' => array(
				'Rocketeer\Tasks\Deploy' => array('after', 'foobar'),
			),
		));

		return $config;
	}

	/**
	 * Mock the Remote component
	 *
	 * @return Mockery
	 */
	protected function getRemote()
	{
		$remote = Mockery::mock('Illuminate\Remote\Connection');
		$remote->shouldReceive('into')->andReturn(Mockery::self());
		$remote->shouldReceive('run')->andReturnUsing(function($tasks, $callback) {
			$task = implode(' && ', $tasks);
			$output = shell_exec($task);

			$callback($output);
		});

		return $remote;
	}

}