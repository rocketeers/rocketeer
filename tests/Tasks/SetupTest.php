<?php

class SetupTest extends RocketeerTests
{
	public function testCanSetupServer()
	{
		$this->mockReleases(function($mock) {
			return $mock
				->shouldReceive('getCurrentRelease')->andReturn(null)
				->shouldReceive('getCurrentReleasePath')->andReturn('1');
		});

		$task = $this->pretendTask('Setup');
		$test = $task->execute();

		$matcher = array(
			"mkdir $this->server/",
			"mkdir -p $this->server/releases",
			"mkdir -p $this->server/current",
			"mkdir -p $this->server/shared",
		);

		$this->assertEquals($matcher, $task->getHistory());
	}

	public function testCanSetupStages()
	{
		$this->mockReleases(function($mock) {
			return $mock
				->shouldReceive('getCurrentRelease')->andReturn(null)
				->shouldReceive('getCurrentReleasePath')->andReturn('1');
		});
		$this->swapConfig(array(
			'rocketeer::stages.stages' => array('staging', 'production'),
		));

		$task = $this->pretendTask('Setup');
		$task->execute();

		$matcher = array(
			"mkdir $this->server/",
			"mkdir -p $this->server/staging/releases",
			"mkdir -p $this->server/staging/current",
			"mkdir -p $this->server/staging/shared",
			"mkdir -p $this->server/production/releases",
			"mkdir -p $this->server/production/current",
			"mkdir -p $this->server/production/shared",
		);

		$this->assertEquals($matcher, $task->getHistory());
	}
}
