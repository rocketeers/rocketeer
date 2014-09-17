<?php
namespace Rocketeer\Services\Storages;

use Rocketeer\TestCases\RocketeerTestCase;

class LocalStorageTest extends RocketeerTestCase
{
	////////////////////////////////////////////////////////////////////
	//////////////////////////////// TESTS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	public function testCanDestroyFile()
	{
		$file = $this->localStorage->getFilepath();
		$this->localStorage->destroy();

		$this->assertFileNotExists($file);
	}

	public function testCanCreateDeploymentsFileAnywhere()
	{
		$this->app['path.storage'] = null;
		$this->app->offsetUnset('path.storage');

		new LocalStorage($this->app);

		$storage = $this->paths->getRocketeerConfigFolder();
		$exists  = file_exists($storage);
		$this->files->deleteDirectory($storage);
		$this->assertTrue($exists);
	}

	public function testCanGetLineEndings()
	{
		$this->localStorage->destroy();

		$this->assertEquals(PHP_EOL, $this->localStorage->getLineEndings());
	}

	public function testCanGetSeparators()
	{
		$this->localStorage->destroy();

		$this->assertEquals(DIRECTORY_SEPARATOR, $this->localStorage->getSeparator());
	}

	public function testCanComputeHashAccordingToContentsOfFiles()
	{
		$this->mockFiles(function ($mock) {
			return $mock
				->shouldReceive('put')->once()
				->shouldReceive('exists')->twice()->andReturn(false)
				->shouldReceive('glob')->once()->andReturn(['foo', 'bar'])
				->shouldReceive('getRequire')->once()->with('foo')->andReturn(['foo'])
				->shouldReceive('getRequire')->once()->with('bar')->andReturn(['bar']);
		});

		$storage = new LocalStorage($this->app, 'deployments', $this->server);
		$hash    = $storage->getHash();

		$this->assertEquals(md5('["foo"]["bar"]'), $hash);
	}

	public function testCanSwitchFolder()
	{
		$storage = new LocalStorage($this->app, 'foo', '/foo');
		$storage->setFolder($this->server);
		$file = $storage->getFilepath();

		$this->assertEquals($this->server, $storage->getFolder());
		$this->assertEquals($this->server.'/foo.json', $file);
	}
}
