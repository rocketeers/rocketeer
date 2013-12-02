<?php

class CurrentReleaseTest extends RocketeerTests
{
	public function testCanGetCurrentRelease()
	{
		$this->app['rocketeer.releases'] = $this->mockCurrentRelease('20000000000000')
			->shouldReceive('getCurrentReleasePath')->once()
			->mock();
		$current = $this->task('CurrentRelease')->execute();
		$this->assertContains('20000000000000', $current);

		$this->app['rocketeer.releases'] = $this->mockCurrentRelease(null)->mock();
		$current = $this->task('CurrentRelease')->execute();
		$this->assertEquals('No release has yet been deployed', $current);
	}

	/**
	 * Mock the current release to return
	 *
	 * @param string $release
	 *
	 * @return void
	 */
	protected function mockCurrentRelease($release)
	{
		return Mockery::mock('ReleasesManager')->shouldReceive('getCurrentRelease')->once()->andReturn($release);
	}
}
