<?php
include __DIR__.'/../vendor/autoload.php';

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

		// Laravel classes --------------------------------------------- /

		$this->app['path.base']    = '/src';
		$this->app['path.storage'] = '/src/storage';

		$this->app['files']   = new Filesystem;
		$this->app['config']  = $this->getConfig();
		$this->app['remote']  = $this->getRemote();
		$this->app['artisan'] = $this->getArtisan();

		// Rocketeer classes ------------------------------------------- /

		$serviceProvider = new RocketeerServiceProvider($this->app);
		$this->app = $serviceProvider->bindClasses($this->app);
		$this->app = $serviceProvider->bindScm($this->app);

		$this->app->bind('rocketeer.server', function ($app) {
			return new Rocketeer\Server($app, __DIR__);
		});

		$command = $this->getCommand();
		$this->app->singleton('rocketeer.tasks', function ($app) use ($command) {
			return new Rocketeer\TasksQueue($app, $command);
		});

		// Bind dummy Task
		$this->task = $this->task('Cleanup');
		$this->recreateVirtualServer();
	}

	/**
	 * Tears down the tests
	 *
	 * @return void
	 */
	public function tearDown()
	{
		Mockery::close();
	}

	/**
	 * Recreates the local file server
	 *
	 * @return void
	 */
	protected function recreateVirtualServer()
	{
		// Recreate deployments file
		$this->app['files']->put($this->deploymentsFile, json_encode(array(
			"foo"                 => "bar",
			"current_release"     => 20000000000000,
			"directory_separator" => "/",
			"is_setup"            => true,
			"apache"              => array("username" => "www-datda","group" => "www-datda"),
			"line_endings"        => "\n",
		)));

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
	 * Get a pretend Task to run bogus commands
	 *
	 * @return Task
	 */
	protected function pretendTask($task = 'Deploy')
	{
		$command = clone $this->getCommand();
		$command->shouldReceive('option')->with('pretend')->andReturn(true);
		$command->shouldReceive('option')->with('verbose')->andReturn(false);

		$task = $this->task($task);
		$task->command = $command;

		return $task;
	}

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
		$message = function ($message) {
			return $message;
		};

		$command = Mockery::mock('Command');
		$command->shouldReceive('comment')->andReturnUsing($message);
		$command->shouldReceive('error')->andReturnUsing($message);
		$command->shouldReceive('line')->andReturnUsing($message);
		$command->shouldReceive('info')->andReturnUsing($message);
		$command->shouldReceive('argument');
		$command->shouldReceive('ask');
		$command->shouldReceive('secret');
		$command->shouldReceive('option')->andReturn(null)->byDefault();

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
		$config->shouldReceive('get')->with('rocketeer::remote.apache')->andReturn(array('user' => 'www-data', 'group' => 'www-data'));
		$config->shouldReceive('get')->with('rocketeer::remote.application_name')->andReturn('foobar');
		$config->shouldReceive('get')->with('rocketeer::remote.root_directory')->andReturn(__DIR__.'/server/');
		$config->shouldReceive('get')->with('rocketeer::remote.keep_releases')->andReturn(1);
		$config->shouldReceive('get')->with('rocketeer::remote.shared')->andReturn(array('tests/meta'));
		$config->shouldReceive('get')->with('rocketeer::connections')->andReturn('production');
		$config->shouldReceive('get')->with('rocketeer::stages.stages')->andReturn(array());
		$config->shouldReceive('get')->with('rocketeer::stages.default')->andReturn(null);

		// SCM
		$config->shouldReceive('get')->with('rocketeer::scm.scm')->andReturn('git');
		$config->shouldReceive('get')->with('rocketeer::scm.branch')->andReturn('master');
		$config->shouldReceive('get')->with('rocketeer::scm.repository')->andReturn('https://github.com/Anahkiasen/rocketeer.git');

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
		$run = function ($task, $callback) {
			if (is_array($task)) {
				$task = implode(' && ', $task);
			}
			$output = shell_exec($task);

			$callback($output);
		};

		$remote = Mockery::mock('Illuminate\Remote\Connection');
		$remote->shouldReceive('into')->andReturn(Mockery::self());
		$remote->shouldReceive('status')->andReturn(0)->byDefault();
		$remote->shouldReceive('run')->andReturnUsing($run)->byDefault();
		$remote->shouldReceive('runRemoteCommands')->andReturnUsing($run)->byDefault();

		return $remote;
	}

	/**
	 * Mock Artisan
	 *
	 * @return Mockery
	 */
	protected function getArtisan()
	{
		$artisan = Mockery::mock('Artisan');
		$artisan->shouldReceive('add')->andReturnUsing(function ($command) {
			return $command;
		});

		return $artisan;
	}
}
