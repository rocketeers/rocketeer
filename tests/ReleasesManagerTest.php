<?php
class ReleasesManagerTest extends RocketeerTests
{

	public function testCanGetCurrentRelease()
	{
		$currentRelease = $this->app['rocketeer.releases']->getCurrentRelease();

		$this->assertEquals(2000000000, $currentRelease);
	}

	public function testCanGetReleasesPath()
	{
		$releasePath = $this->app['rocketeer.releases']->getReleasesPath();

		$this->assertEquals($this->server.'/releases', $releasePath);
	}

	public function testCanGetCurrentReleaseFolder()
	{
		$currentReleasePath = $this->app['rocketeer.releases']->getCurrentReleasePath();

		$this->assertEquals($this->server.'/releases/2000000000', $currentReleasePath);
	}

	public function testCanGetReleases()
	{
		$releases = $this->app['rocketeer.releases']->getReleases();

		$this->assertEquals(array(1 => 1000000000, 0 => 2000000000), $releases);
	}

	public function testCanGetDeprecatedReleases()
	{
		$releases = $this->app['rocketeer.releases']->getDeprecatedReleases();

		$this->assertEquals(array(1000000000), $releases);
	}

	public function testCanGetPreviousRelease()
	{
		$currentRelease = $this->app['rocketeer.releases']->getPreviousRelease();

		$this->assertEquals(1000000000, $currentRelease);
	}

	public function testCanUpdateCurrentRelease()
	{
		$this->app['rocketeer.releases']->updateCurrentRelease(3000000000);

		$this->assertEquals(3000000000, $this->app['rocketeer.server']->getValue('current_release'));
	}

}
