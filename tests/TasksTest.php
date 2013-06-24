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
		$this->assertTrue($tests);

		$tests = $task->runTests('--fail');
		$this->assertFalse($tests);

		$this->app['files']->delete($this->server.'/current');
		$this->app['files']->deleteDirectory($this->server);
	}

	public function testCanGetBinaryWithFallback()
	{
		$task = $this->task('Deploy');

		$grep = $task->which('grep');
		$this->assertEquals('/usr/bin/grep', $grep);

		$grep = $task->which('grsdg', '/usr/bin/grep');
		$this->assertEquals('/usr/bin/grep', $grep);

		$this->assertFalse($task->which('fdsf'));
	}

	public function testCanDisplayOutputOfCommandsIfVerbose()
	{
		$task = $this->task('Deploy');
		$command = $this->getCommand(false);
		$command->shouldReceive('option')->with('verbose')->andReturn(true);
		$command->shouldReceive('option')->with('pretend')->andReturn(false);
		$task->command = $command;

		ob_start();
			$task->run('ls');
		$output = ob_get_clean();

		$this->assertContains('tests', $output);
	}

	public function testCanPretendToRunTasks()
	{
		$task = $this->task('Cleanup');
		$command = $this->getCommand(false);
		$command->shouldReceive('option')->with('pretend')->andReturn(true);
		$task->command = $command;

		$output = $task->run('ls');
		$this->assertEquals('ls', $output);
	}

}
