<?php
namespace Rocketeer\Tasks;

use Rocketeer\TestCases\RocketeerTestCase;

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
			'git --version',
			'{php} -m',
			"mkdir {server}/",
			"mkdir -p {server}/releases",
			"mkdir -p {server}/current",
			"mkdir -p {server}/shared",
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
			'git --version',
			'{php} -m',
			"mkdir {server}/",
			"mkdir -p {server}/staging/releases",
			"mkdir -p {server}/staging/current",
			"mkdir -p {server}/staging/shared",
			"mkdir -p {server}/production/releases",
			"mkdir -p {server}/production/current",
			"mkdir -p {server}/production/shared",
		));
	}

	public function testRunningSetupKeepsCurrentConfiguredStage()
	{
		$this->mockReleases(function ($mock) {
			return $mock
				->shouldReceive('getCurrentRelease')->andReturn(null)
				->shouldReceive('getCurrentReleasePath')->andReturn('1');
		});
		$this->swapConfig(array(
			'rocketeer::stages.stages' => ['staging', 'production'],
		));

		$this->connections->setStage('staging');
		$this->assertEquals('staging', $this->connections->getStage());
		$this->assertTaskHistory('Setup', array(
			'git --version',
			'{php} -m',
			"mkdir {server}/",
			"mkdir -p {server}/staging/releases",
			"mkdir -p {server}/staging/current",
			"mkdir -p {server}/staging/shared",
			"mkdir -p {server}/production/releases",
			"mkdir -p {server}/production/current",
			"mkdir -p {server}/production/shared",
		), array(
			'stage' => 'staging',
		));
		$this->assertEquals('staging', $this->connections->getStage());
	}
}
