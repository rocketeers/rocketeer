<?php
namespace Rocketeer\Tests\Tasks;

use Rocketeer\Tests\TestCases\RocketeerTestCase;

class SetupTest extends RocketeerTestCase
{
	public function testCanSetupServer()
	{
		$this->mockReleases(function ($mock) {
			return $mock
				->shouldReceive('getCurrentRelease')->andReturn(null)
				->shouldReceive('getCurrentReleasePath')->andReturn('1');
		});

		$this->assertTaskHistory('Setup', array(
			"mkdir $this->server/",
			"mkdir -p $this->server/releases",
			"mkdir -p $this->server/current",
			"mkdir -p $this->server/shared",
		));
	}

	public function testCanSetupStages()
	{
		$this->mockReleases(function ($mock) {
			return $mock
				->shouldReceive('getCurrentRelease')->andReturn(null)
				->shouldReceive('getCurrentReleasePath')->andReturn('1');
		});
		$this->swapConfig(array(
			'rocketeer::stages.stages' => array('staging', 'production'),
		));

		$this->assertTaskHistory('Setup', array(
			"mkdir $this->server/",
			"mkdir -p $this->server/staging/releases",
			"mkdir -p $this->server/staging/current",
			"mkdir -p $this->server/staging/shared",
			"mkdir -p $this->server/production/releases",
			"mkdir -p $this->server/production/current",
			"mkdir -p $this->server/production/shared",
		));
	}
}
