<?php

class CurrentReleaseTest extends RocketeerTests
{
	public function testCanGetCurrentRelease()
	{
		$current = $this->task('CurrentRelease')->execute();
		$this->assertContains('20000000000000', $current);

		$this->app['rocketeer.server']->setValue('current_release', 0);
		$current = $this->task('CurrentRelease')->execute();
		$this->assertEquals('No release has yet been deployed', $current);
	}
}
