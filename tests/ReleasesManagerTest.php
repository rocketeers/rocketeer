<?php
class ReleasesManagerTest extends RocketeerTests
{

	public function testCanGetCurrentRelease()
	{
		$this->assertEquals(1371935884, $this->app['rocketeer.releases']->getCurrentRelease());
	}

	public function testCanGetReleasesPath()
	{
		$this->assertEquals('/home/www/foobar/releases', $this->app['rocketeer.releases']->getReleasesPath());
	}

	public function testCanGetCurrentReleaseFolder()
	{
		$this->assertEquals('/home/www/foobar/releases/1371935884', $this->app['rocketeer.releases']->getCurrentReleasePath());
	}

}