<?php
namespace Rocketeer\Tests\Tasks;

use Rocketeer\Tests\RocketeerTests;

class CurrentReleaseTest extends RocketeerTests
{
	public function testCanGetCurrentRelease()
	{
		$this->mockReleases(function ($mock) {
			return $mock
				->shouldReceive('getCurrentRelease')->once()->andReturn('20000000000000')
				->shouldReceive('getCurrentReleasePath')->once();
		});

		$current = $this->task('CurrentRelease')->execute();
		$this->assertContains('20000000000000', $current);
	}

	public function testPrintsMessageIfNoReleaseDeployed()
	{
		$this->mockReleases(function ($mock) {
			return $mock
				->shouldReceive('getCurrentRelease')->once()->andReturn(null)
				->shouldReceive('getCurrentReleasePath')->never();
		});

		$current = $this->task('CurrentRelease')->execute();
		$this->assertEquals('No release has yet been deployed', $current);
	}
}
