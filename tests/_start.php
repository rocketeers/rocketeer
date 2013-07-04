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
	 * The path to the local deployments file
	 *
	 * @var string
	 */
	protected $deploymentsFile;

	/**
	 * A dummy Task to use for helpers tests
	 *
	 * @var Task
	 */
	protected $task;

	/**
	 * Set up the tests
	 *
	 * @return void
	 */
	public function setUp()
	{
		// Setup local server
		$this->server          = __DIR__.'/server/foobar';
		$this->deploymentsFile = __DIR__.'/meta/deployments.json';

		$this->app = new Container;

		// Get the Mockery instances
		$command = $this->getCommand();
		$config  = $this->getConfig();
		$remote  = $this->getRemote();

		// Laravel classes --------------------------------------------- /

		$this->app['path.base']    = '/src';
		$this->app['path.storage'] = '/src/storage';

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

		$serviceProvider = new RocketeerServiceProvider($this->app);
		$this->app = $serviceProvider->bindClasses($this->app);
		$this->app = $serviceProvider->bindScm($this->app);

		$this->app->bind('rocketeer.server', function($app) {
			return new Rocketeer\Server($app, __DIR__);
		});

		$this->app->singleton('rocketeer.tasks', function($app) use ($command) {
			return new Rocketeer\TasksQueue($app, $command);
		});

		// Bind dummy Task
		$this->task = $this->task('Cleanup');
	}

	/**
	 * Recreate placeholder server
	 *
	 * @return void
	 */
	public function tearDown()
	{
		// Recreate deployments file
		$deployments = array('foo' => 'bar', 'current_release' => 20000000000000);
		$this->app['files']->put($this->deploymentsFile, json_encode($deployments));

		// Recreate altered local server
		$folders = array('current', 'shared', 'releases', 'releases/10000000000000', 'releases/20000000000000');
		foreach ($folders as $folder) {
			$folder = $this->server.'/'.$folder;

			$this->app['files']->deleteDirectory($folder);
			$this->app['files']->delete($folder);
			$this->app['files']->makeDirectory($folder, 0777, true);
			file_put_contents($folder.'/.gitkeep', '');
		}

	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get Task instance
	 *
	 * @param  string $task
	 *
	 * @return Task
	 */
	protected function task($task = null)
	{
		if (!$task) {
			return $this->task;
		}

		return $this->tasksQueue()->buildTask('Rocketeer\Tasks\\'.$task);
	}

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
	protected function getCommand($option = true)
	{
		$command = Mockery::mock('Illuminate\Console\Command');
		$command->shouldReceive('comment')->andReturnUsing(function($message) { return $message; });
		$command->shouldReceive('error')->andReturnUsing(function($message) { return $message; });
		$command->shouldReceive('line')->andReturnUsing(function($message) { return $message; });
		$command->shouldReceive('info')->andReturnUsing(function($message) { return $message; });
		$command->shouldReceive('argument');
		$command->shouldReceive('ask');
		$command->shouldReceive('secret');
		if ($option) $command->shouldReceive('option');

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

		// Drivers
		$config->shouldReceive('get')->with('database.default')->andReturn('mysql');
		$config->shouldReceive('get')->with('cache.driver')->andReturn('file');
		$config->shouldReceive('get')->with('session.driver')->andReturn('file');

		// Rocketeer
		$config->shouldReceive('get')->with('rocketeer::remote.application_name')->andReturn('foobar');
		$config->shouldReceive('get')->with('rocketeer::remote.root_directory')->andReturn(__DIR__.'/server/');
		$config->shouldReceive('get')->with('rocketeer::remote.keep_releases')->andReturn(1);
		$config->shouldReceive('get')->with('rocketeer::remote.shared')->andReturn(array('tests/meta'));
		$config->shouldReceive('get')->with('rocketeer::connections')->andReturn('production');
		$config->shouldReceive('get')->with('rocketeer::stages.stages')->andReturn(array());
		$config->shouldReceive('get')->with('rocketeer::stages.default')->andReturn(null);

		// SCM
		$config->shouldReceive('get')->with('rocketeer::git.branch')->andReturn('master');
		$config->shouldReceive('get')->with('rocketeer::git.repository')->andReturn('https://github.com/Anahkiasen/rocketeer.git');

		// Tasks
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
		$run = function($task, $callback) {
			if (is_array($task)) $task = implode(' && ', $task);
			$output = shell_exec($task);

			$callback($output);
		};

		$remote = Mockery::mock('Illuminate\Remote\Connection');
		$remote->shouldReceive('into')->andReturn(Mockery::self());
		$remote->shouldReceive('status')->andReturn(0)->byDefault();
		$remote->shouldReceive('run')->andReturnUsing($run);
		$remote->shouldReceive('runRemoteCommands')->andReturnUsing($run);

		return $remote;
	}

}
