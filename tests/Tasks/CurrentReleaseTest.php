<?php
namespace Rocketeer\Tests\Tasks;

use Rocketeer\Tests\TestCases\RocketeerTestCase;

class CurrentReleaseTest extends RocketeerTestCase
{
	public function testCanGetCurrentRelease()
	{
		$this->mockReleases(function ($mock) {
			return $mock
				->shouldReceive('getCurrentRelease')->once()->andReturn('20000000000000')
				->shouldReceive('getCurrentReleasePath')->once();
		});

		$this->assertTaskOutput('CurrentRelease', '20000000000000');
	}

	public function testPrintsMessageIfNoReleaseDeployed()
	{
		$this->mockReleases(function ($mock) {
			return $mock
				->shouldReceive('getCurrentRelease')->once()->andReturn(null)
				->shouldReceive('getCurrentReleasePath')->never();
		});

		$this->assertTaskOutput('CurrentRelease', 'No release has yet been deployed');
	}
}
