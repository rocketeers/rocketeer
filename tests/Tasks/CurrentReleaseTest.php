<?php
namespace Rocketeer\Tasks;

use Rocketeer\TestCases\RocketeerTestCase;

class CurrentReleaseTest extends RocketeerTestCase
{
	public function testCanGetCurrentRelease()
	{
		$this->mockReleases(function ($mock) {
			return $mock
				->shouldReceive('getValidationFile')->once()->andReturn(array(10000000000000 => true))
				->shouldReceive('getCurrentRelease')->once()->andReturn('20000000000000')
				->shouldReceive('getCurrentReleasePath')->once();
		});

		$this->assertTaskOutput('CurrentRelease', '20000000000000');
	}

	public function testPrintsMessageIfNoReleaseDeployed()
	{
		$this->mockReleases(function ($mock) {
			return $mock
				->shouldReceive('getValidationFile')->never()
				->shouldReceive('getCurrentRelease')->once()->andReturn(null)
				->shouldReceive('getCurrentReleasePath')->never();
		});

		$this->assertTaskOutput('CurrentRelease', 'No release has yet been deployed');
	}
}
