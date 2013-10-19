<?php

class CleanupTest extends RocketeerTests
{
	public function testCanCleanupServer()
	{
		$cleanup = $this->task('Cleanup');
		$output  = $cleanup->execute();

		$this->assertFileNotExists($this->server.'/releases/10000000000000');
		$this->assertEquals('Removing <info>1 release</info> from the server', $output);

		$output = $cleanup->execute();
		$this->assertEquals('No releases to prune from the server', $output);
	}
}
