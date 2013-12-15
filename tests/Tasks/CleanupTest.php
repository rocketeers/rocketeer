<?php

class CleanupTest extends RocketeerTests
{
	public function testCanCleanupServer()
	{
		$this->mockReleases(function ($mock) {
			return $mock
				->shouldReceive('getDeprecatedReleases')->once()->andReturn(array(1, 2))
				->shouldReceive('getPathToRelease')->times(2)->andReturnUsing(function ($release) {
					return $release;
				});
		});

		$output = $this->pretendTask('Cleanup')->execute();
		$this->assertEquals('Removing <info>2 releases</info> from the server', $output);
	}

	public function testPrintsMessageIfNoCleanup()
	{
		$this->mockReleases(function ($mock) {
			return $mock->shouldReceive('getDeprecatedReleases')->once()->andReturn(array());
		});

		$output = $this->pretendTask('Cleanup')->execute();
		$this->assertEquals('No releases to prune from the server', $output);
	}
}
