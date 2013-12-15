<?php
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Rocketeer\RocketeerServiceProvider;

abstract class TestsContainer extends PHPUnit_Framework_TestCase
{
	/**
	 * The IoC Container
	 *
	 * @var Container
	 */
	protected $app;

	/**
	 * Set up the tests
	 *
	 * @return void
	 */
	public function setUp()
	{
		$this->app = new Container;

		// Laravel classes --------------------------------------------- /

		$this->app->instance('path.base',    '/src');
		$this->app->instance('path',         '/src/app');
		$this->app->instance('path.public',  '/src/public');
		$this->app->instance('path.storage', '/src/app/storage');

		$this->app['files']             = new Filesystem;
		$this->app['config']            = $this->getConfig();
		$this->app['remote']            = $this->getRemote();
		$this->app['artisan']           = $this->getArtisan();
		$this->app['rocketeer.command'] = $this->getCommand();

		// Rocketeer classes ------------------------------------------- /

		$serviceProvider = new RocketeerServiceProvider($this->app);
		$this->app = $serviceProvider->bindPaths($this->app);
		$this->app = $serviceProvider->bindCoreClasses($this->app);
		$this->app = $serviceProvider->bindClasses($this->app);
		$this->app = $serviceProvider->bindScm($this->app);

		$this->app->bind('rocketeer.server', function ($app) {
			return new Rocketeer\Server($app, 'deployments', __DIR__.'/meta');
		});

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

	////////////////////////////////////////////////////////////////////
	///////////////////////// MOCKED INSTANCES /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Bind a mocked instance in the Container
	 *
	 * @param string  $handle
	 * @param string  $class
	 * @param Closure $expectations
	 *
	 * @return Mockery
	 */
	protected function mock($handle, $class, $expectations)
	{
		$mockery = Mockery::mock($class);
		$mockery = $expectations($mockery)->mock();

		$this->app[$handle] = $mockery;

		return $mockery;
	}

	/**
	 * Mock the Command class
	 *
	 * @return Mockery
	 */
	protected function getCommand()
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
		$command->shouldReceive('confirm')->andReturn(true);
		$command->shouldReceive('secret');
		$command->shouldReceive('option')->andReturn(null)->byDefault();

		return $command;
	}

	/**
	 * Mock the Config component
	 *
	 * @return Mockery
	 */
	protected function getConfig($options = array())
	{
		$config = Mockery::mock('Illuminate\Config\Repository');
		$config->shouldIgnoreMissing();

		foreach ($options as $key => $value) {
			$config->shouldReceive('get')->with($key)->andReturn($value);
		}

		// Drivers
		$config->shouldReceive('get')->with('cache.driver')->andReturn('file');
		$config->shouldReceive('get')->with('database.default')->andReturn('mysql');
		$config->shouldReceive('get')->with('remote.default')->andReturn('production');
		$config->shouldReceive('get')->with('remote.connections')->andReturn(array('production' => array(), 'staging' => array()));
		$config->shouldReceive('get')->with('session.driver')->andReturn('file');

		// Rocketeer
		$config->shouldReceive('get')->with('rocketeer::default')->andReturn(array('production', 'staging'));
		$config->shouldReceive('get')->with('rocketeer::connections')->andReturn(array());
		$config->shouldReceive('get')->with('rocketeer::remote.application_name')->andReturn('foobar');
		$config->shouldReceive('get')->with('rocketeer::remote.keep_releases')->andReturn(1);
		$config->shouldReceive('get')->with('rocketeer::remote.permissions')->andReturn(array(
			'permissions' => 755,
			'webuser' => array('user' => 'www-data', 'group' => 'www-data')
		));
		$config->shouldReceive('get')->with('rocketeer::remote.permissions.files')->andReturn(array('tests'));
		$config->shouldReceive('get')->with('rocketeer::remote.root_directory')->andReturn(__DIR__.'/server/');
		$config->shouldReceive('get')->with('rocketeer::remote.shared')->andReturn(array('tests/Elements'));
		$config->shouldReceive('get')->with('rocketeer::stages.default')->andReturn(null);
		$config->shouldReceive('get')->with('rocketeer::stages.stages')->andReturn(array());

		// SCM
		$config->shouldReceive('get')->with('rocketeer::scm.branch')->andReturn('master');
		$config->shouldReceive('get')->with('rocketeer::scm.repository')->andReturn('https://github.com/'.$this->repository);
		$config->shouldReceive('get')->with('rocketeer::scm.scm')->andReturn('git');

		// Tasks
		$config->shouldReceive('get')->with('rocketeer::hooks')->andReturn(array(
			'before' => array(
				'deploy' => array(
					'before',
					'foobar'
				),
			),
			'after' => array(
				'check' => array(
					'Tasks\MyCustomTask',
				),
				'deploy' => array(
					'after',
					'foobar'
				),
			),
		));

		return $config;
	}

	/**
	 * Swap the current config
	 *
	 * @param  array $config
	 *
	 * @return void
	 */
	protected function swapConfig($config)
	{
		$this->app['rocketeer.rocketeer']->disconnect();
		$this->app['config'] = $this->getConfig($config);
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
		$remote->shouldReceive('runRaw')->andReturnUsing($run)->byDefault();
		$remote->shouldReceive('display')->andReturnUsing(function ($line) {
			print $line.PHP_EOL;
		});

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
