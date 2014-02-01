<?php
namespace Rocketeer;

use Rocketeer\TestCases\RocketeerTestCase;

class ReleasesManagerTest extends RocketeerTestCase
{
	public function testCanGetCurrentRelease()
	{
		$currentRelease = $this->app['rocketeer.releases']->getCurrentRelease();

		$this->assertEquals(20000000000000, $currentRelease);
	}

	public function testCanGetCurrentReleaseFromServerIfUncached()
	{
		$this->mock('rocketeer.server', 'Server', function ($mock) {
			return $mock
				->shouldReceive('getValue')->with('current_release.production')->once()->andReturn(null)
				->shouldReceive('setValue')->with('current_release.production', '20000000000000')->once()
				->shouldReceive('getSeparator')->andReturn('/')
				->shouldReceive('getLineEndings')->andReturn(PHP_EOL);
		});

		$currentRelease = $this->app['rocketeer.releases']->getCurrentRelease();

		$this->assertEquals(20000000000000, $currentRelease);
	}

	public function testCanGetReleasesPath()
	{
		$releasePath = $this->app['rocketeer.releases']->getReleasesPath();

		$this->assertEquals($this->server.'/releases', $releasePath);
	}

	public function testCanGetCurrentReleaseFolder()
	{
		$currentReleasePath = $this->app['rocketeer.releases']->getCurrentReleasePath();

		$this->assertEquals($this->server.'/releases/20000000000000', $currentReleasePath);
	}

	public function testCanGetReleases()
	{
		$releases = $this->app['rocketeer.releases']->getReleases();

		$this->assertEquals(array(1 => 10000000000000, 0 => 20000000000000), $releases);
	}

	public function testCanGetDeprecatedReleases()
	{
		$releases = $this->app['rocketeer.releases']->getDeprecatedReleases();

		$this->assertEquals(array(10000000000000), $releases);
	}

	public function testCanGetPreviousRelease()
	{
		$currentRelease = $this->app['rocketeer.releases']->getPreviousRelease();

		$this->assertEquals(10000000000000, $currentRelease);
	}

	public function testCanUpdateCurrentRelease()
	{
		$this->app['rocketeer.releases']->updateCurrentRelease(30000000000000);

		$this->assertEquals(30000000000000, $this->app['rocketeer.server']->getValue('current_release.production'));
	}

	public function testCanGetFolderInRelease()
	{
		$folder = $this->app['rocketeer.releases']->getCurrentReleasePath('{path.storage}');

		$this->assertEquals($this->server.'/releases/20000000000000/app/storage', $folder);
	}
}
