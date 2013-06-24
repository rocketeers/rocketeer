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

		$this->assertContains('2000000000', $current);
	}

	public function testCanTeardownServer()
	{
		$output = $this->task('Teardown')->execute();

		$this->assertFileNotExists($this->deploymentsFile);
		$this->assertFileNotExists($this->server);
	}

	public function testCanRollbackRelease()
	{
		$output = $this->task('Rollback')->execute();

		$this->assertEquals(1000000000, $this->app['rocketeer.releases']->getCurrentRelease());
	}

	public function testCanSetupServer()
	{
		$this->app['files']->deleteDirectory($this->server);
		$output = $this->task('Setup')->execute();

		$this->assertFileExists($this->server);
		$this->assertFileExists($this->server.'/current');
		$this->assertFileExists($this->server.'/releases');
	}

}
