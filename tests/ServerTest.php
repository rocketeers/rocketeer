<?php

class ServerTest extends RocketeerTests
{

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// TESTS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	public function testCanCreateDeploymentsFileAnywhere()
	{
		$this->app['path.storage'] = null;
		$this->app->offsetUnset('path.storage');

		new Rocketeer\Server($this->app);

		$storage = __DIR__.'/../storage';
		$exists = file_exists($storage);
		$this->app['files']->deleteDirectory($storage);
		$this->assertTrue($exists);
	}

	public function testCanGetValueFromDeploymentsFile()
	{
		$this->assertEquals('bar', $this->app['rocketeer.server']->getValue('foo'));
	}

	public function testCanSetValueInDeploymentsFile()
	{
		$this->app['rocketeer.server']->setValue('foo', 'baz');

		$this->assertEquals('baz', $this->app['rocketeer.server']->getValue('foo'));
	}

	public function testCandeleteRepository()
	{
		$this->app['rocketeer.server']->deleteRepository();

		$this->assertFalse($this->app['files']->exists(__DIR__.'/meta/deployments.json'));
	}

	public function testCanFallbackIfFileDoesntExist()
	{
		$this->app['rocketeer.server']->deleteRepository();

		$this->assertEquals(null, $this->app['rocketeer.server']->getValue('foo'));
	}

	public function testCanGetLineEndings()
	{
		$this->app['rocketeer.server']->deleteRepository();

		$this->assertEquals(PHP_EOL, $this->app['rocketeer.server']->getLineEndings());
	}

	public function testCanGetSeparators()
	{
		$this->app['rocketeer.server']->deleteRepository();

		$this->assertEquals(DIRECTORY_SEPARATOR, $this->app['rocketeer.server']->getSeparator());
	}
}
