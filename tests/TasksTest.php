<?php
class TasksTest extends RocketeerTests
{

	public function testCanCleanupServer()
	{
		$cleanup = $this->task('Cleanup');
		$output  = $cleanup->execute();

		$this->assertFileNotExists($this->server.'/releases/1000000000');
		$this->assertEquals('Removing 1 release from the server', $output);

		$output = $cleanup->execute();
		$this->assertEquals('No releases to prune from the server', $output);
	}

	public function testCanGetCurrentRelease()
	{
		$current = $this->task('CurrentRelease')->execute();

		$this->assertTrue(str_contains($current, '2000000000'));
	}

	public function testCanTeardownServer()
	{
		$output = $this->task('Teardown')->execute();

		$this->assertFileNotExists($this->deploymentsFile);
		$this->assertFileNotExists($this->server);
	}

}
