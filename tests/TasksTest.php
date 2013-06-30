<?php
class TasksTest extends RocketeerTests
{

	public function testCanGetDescription()
	{
		$task = $this->task('Setup');

		$this->assertNotNull($task->getDescription());
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// TASKS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	public function testCanCleanupServer()
	{
		$cleanup = $this->task('Cleanup');
		$output  = $cleanup->execute();

		$this->assertFileNotExists($this->server.'/releases/1000000000');
		$this->assertEquals('Removing <info>1 release</info> from the server', $output);

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
			'repository' => 'https://github.com/Anahkiasen/rocketeer.git',
			'username'   => '',
			'password'   => '',
		));

		$output  = $this->task('Deploy')->execute();
		$release = $this->app['rocketeer.releases']->getCurrentRelease();

		$releasePath = $this->server.'/releases/'.$release;
		$this->assertFileExists($this->server.'/shared/tests/meta/deployments.json');
		$this->assertFileExists($releasePath);
		$this->assertFileExists($releasePath.'/.git');
		$this->assertFileExists($releasePath.'/vendor');
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * @depends testCanDeployToServer
	 */
	public function testCanRunTests()
	{
		$release = glob($this->server.'/releases/*');
		$release = basename($release[1]);
		$this->task->releasesManager->updateCurrentRelease($release);

		$tests = $this->task->runTests('tests/DeploymentsManagerTest.php');
		$this->assertTrue($tests);

		$tests = $this->task->runTests('--fail');
		$this->assertFalse($tests);

		$this->app['files']->delete($this->server.'/current');
		system('rm -rf '.$this->server);
	}

	public function testCanUpdateRepository()
	{
		$output = $this->task->updateRepository();

		$this->assertContains('Current branch develop is up to date', $output);
	}

	public function testCanGetBinaryWithFallback()
	{
		$grep = $this->task->which('grep');
		$this->assertTrue(in_array($grep, array('/bin/grep', '/usr/bin/grep')));

		$grep = $this->task->which('grsdg', '/usr/bin/grep');
		$this->assertEquals('/usr/bin/grep', $grep);

		$this->assertFalse($this->task->which('fdsf'));
	}

	public function testCanDisplayOutputOfCommandsIfVerbose()
	{
		$command = $this->getCommand(false);
		$command->shouldReceive('option')->with('verbose')->andReturn(true);
		$command->shouldReceive('option')->with('pretend')->andReturn(false);
		$this->task->command = $command;

		ob_start();
			$this->task->run('ls');
		$output = ob_get_clean();

		$this->assertContains('tests', $output);
	}

	public function testCanPretendToRunTasks()
	{
		$command = $this->getCommand(false);
		$command->shouldReceive('option')->with('pretend')->andReturn(true);
		$this->task->command = $command;

		$output = $this->task->run('ls');
		$this->assertEquals('ls', $output);
	}

	public function testCanListContentsOfAFolder()
	{
		$contents = $this->task->listContents($this->server);

		$this->assertEquals(array('current', 'releases', 'shared'), $contents);
	}

	public function testCanCheckIfFileExists()
	{
		$this->assertTrue($this->task->fileExists($this->server));
		$this->assertFalse($this->task->fileExists($this->server.'/nope'));
	}

}
