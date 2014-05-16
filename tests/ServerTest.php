<?php
namespace Rocketeer;

use Rocketeer\TestCases\RocketeerTestCase;

class ServerTest extends RocketeerTestCase
{
	////////////////////////////////////////////////////////////////////
	//////////////////////////////// TESTS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	public function testCanCreateDeploymentsFileAnywhere()
	{
		$this->app['path.storage'] = null;
		$this->app->offsetUnset('path.storage');

		new Server($this->app);

		$storage = $this->app['rocketeer.rocketeer']->getRocketeerConfigFolder();
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

		$this->assertFalse($this->app['files']->exists(__DIR__.'/_meta/deployments.json'));
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

	public function testCanComputeHashAccordingToContentsOfFiles()
	{
		$this->mock('files', 'Filesystem', function ($mock) {
			return $mock
				->shouldReceive('put')->once()
				->shouldReceive('exists')->twice()->andReturn(false)
				->shouldReceive('glob')->once()->andReturn(array('foo', 'bar'))
				->shouldReceive('getRequire')->once()->with('foo')->andReturn(array('foo'))
				->shouldReceive('getRequire')->once()->with('bar')->andReturn(array('bar'));
		});

		$hash = $this->app['rocketeer.server']->getHash();

		$this->assertEquals(md5('["foo"]["bar"]'), $hash);
	}

	public function testCanCheckIfComposerIsNeeded()
	{
		$this->usesComposer(true);
		$this->assertTrue($this->app['rocketeer.server']->usesComposer());

		$this->usesComposer(false);
		$this->assertFalse($this->app['rocketeer.server']->usesComposer());
	}
}
