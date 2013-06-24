<?php
class TasksTest extends RocketeerTests
{

	public function testCanCleanupServer()
	{
		$cleanup = $this->task('Cleanup');
		$output  = $cleanup->execute();

		$this->assertFileNotExists($this->server.'/releases/1000000000');
		$this->assertEquals('Removing <success>1 release</success> from the server', $output);

		$output = $cleanup->execute();
		$this->assertEquals('No releases to prune from the server', $output);
	}

	public function testCanGetCurrentRelease()
	{
		$current = $this->task('CurrentRelease')->execute();
		$this->assertContains('2000000000', $current);

		$this->app['rocketeer.deployments']->setValue('current_release', 0);
		$current = $this->task('CurrentRelease')->execute();
		$this->assertEquals('No release has yet been deployed', $current);
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

	public function testCanDeployToServer()
	{
		$this->app['config']->shouldReceive('get')->with('rocketeer::git')->andReturn(array(
			'repository' => 'git://github.com/Anahkiasen/rocketeer.git',
			'username'   => '',
			'password'   => '',
		));

		$output  = $this->task('Deploy')->execute();
		$release = substr($output, -10);

		$releasePath = $this->server.'/releases/'.$release;
		$this->assertFileExists($releasePath);
		$this->assertFileExists($releasePath.'/.git');
		$this->assertFileExists($releasePath.'/vendor');
	}

	public function testCanRunTests()
	{
		$release = glob($this->server.'/releases/*');
		$release = basename($release[1]);

		$task = $this->task('Deploy');
		$task->releasesManager->updateCurrentRelease($release);
		$tests = $task->runTests('tests/DeploymentsManagerTest.php');

		$this->app['files']->delete($this->server.'/current');
		$this->app['files']->deleteDirectory($this->server);

		$this->assertTrue($tests);
	}

}
