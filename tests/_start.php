<?php
use Illuminate\Container\Container;

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
		$me = $this;
		$this->app = new Container;

		$this->app->singleton('config', function() use ($me) {
			return $me->getConfig();
		});

		$this->app->singleton('files', function() use ($me) {
			return $me->getFiles();
		});

		$this->app->bind('rocketeer', function($app) {
			return new Rocketeer\Rocketeer($app['config']);
		});

		$this->app->bind('rocketeer.deployments', function($app) {
			return new Rocketeer\DeploymentsManager($app['files'], 'app/storage');
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

	/**
	 * Mock the Filesystem component
	 *
	 * @return Mockery
	 */
	protected function getFiles()
	{
		$files = Mockery::mock('Illuminate\Filesystem\Filesystem');
		$files->shouldReceive('exists')->andReturn(true);
		$files->shouldReceive('get')->andReturn('{"foo":"bar"}');
		$files->shouldReceive('put');

		return $files;
	}

}