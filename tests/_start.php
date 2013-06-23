<?php
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;

abstract class RocketeerTests extends PHPUnit_Framework_TestCase
{

	/**
	 * The IoC Container
	 *
	 * @var Container
	 */
	protected $app;

	/**
	 * Set up the tests
	 */
	public function setUp()
	{
		$this->app = new Container;

		// Get the Mockery instances
		$config = $this->getConfig();

		$this->app->singleton('config', function() use ($config) {
			return $config;
		});

		$this->app->singleton('files', function() {
			return new Filesystem;
		});

		$this->app->bind('rocketeer.rocketeer', function($app) {
			return new Rocketeer\Rocketeer($app['config']);
		});

		$this->app->bind('rocketeer.releases', function($app) {
			return new Rocketeer\ReleasesManager($app);
		});

		$this->app->bind('rocketeer.deployments', function($app) {
			return new Rocketeer\DeploymentsManager($app['files'], __DIR__);
		});
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// DEPENDENCIES /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Mock the Config component
	 *
	 * @return Mockery
	 */
	protected function getConfig()
	{
		$config = Mockery::mock('Illuminate\Config\Repository');
		$config->shouldReceive('get')->with('rocketeer::remote.application_name')->andReturn('foobar');
		$config->shouldReceive('get')->with('rocketeer::remote.root_directory')->andReturn('/home/www/');

		return $config;
	}

}