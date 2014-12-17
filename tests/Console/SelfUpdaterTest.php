<?php
namespace Rocketeer\Console;

use Mockery\MockInterface;
use Rocketeer\TestCases\RocketeerTestCase;

class SelfUpdaterTest extends RocketeerTestCase
{
	public function testCanUpdateToLatestVersion()
	{
		$this->mockFiles(function (MockInterface $mock) {
			return $mock
				->shouldReceive('isWritable')->twice()->andReturn(true)
				->shouldReceive('move')->with($this->paths->getRocketeerConfigFolder().'/bar-latest-temp.phar', '/foo/bar')->once();
		});

		$updater = new SelfUpdater($this->app, '/foo/bar');
		$updater->update();
	}

	public function testCanUpdateToSpecificVersion()
	{
		$this->mockFiles(function (MockInterface $mock) {
			return $mock
				->shouldReceive('isWritable')->twice()->andReturn(true)
				->shouldReceive('move')->with($this->paths->getRocketeerConfigFolder().'/bar-1.0.4-temp.phar', '/foo/bar')->once();
		});

		$updater = new SelfUpdater($this->app, '/foo/bar', '1.0.4');
		$updater->update();
	}
}
