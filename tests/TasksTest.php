<?php

class TasksTest extends RocketeerTests
{

	public function testCanGetDescription()
	{
		$task = $this->task('Setup');

		$this->assertNotNull($task->getDescription());
	}

	public function testCanRunMigrations()
	{
		$task = $this->pretendTask();

		$commands = $task->runMigrations();
		$this->assertEquals('php artisan migrate', $commands[1]);

		$commands = $task->runMigrations(true);
		$this->assertEquals('php artisan migrate --seed', $commands[1]);
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// TASKS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	public function testCanPretendToCheck()
	{
		$task = $this->pretendTask('Check');
		$task->execute();
	}

	public function testCanCleanupServer()
	{
		$cleanup = $this->task('Cleanup');
		$output  = $cleanup->execute();

		$this->assertFileNotExists($this->server.'/releases/10000000000000');
		$this->assertEquals('Removing <info>1 release</info> from the server', $output);

		$output = $cleanup->execute();
		$this->assertEquals('No releases to prune from the server', $output);
	}

	public function testCanGetCurrentRelease()
	{
		$current = $this->task('CurrentRelease')->execute();
		$this->assertContains('20000000000000', $current);

		$this->app['rocketeer.server']->setValue('current_release', 0);
		$current = $this->task('CurrentRelease')->execute();
		$this->assertEquals('No release has yet been deployed', $current);
	}

	public function testCanTeardownServer()
	{
		$this->task('Teardown')->execute();

		$this->assertFileNotExists($this->deploymentsFile);
		$this->assertFileNotExists($this->server);
	}

	public function testCanRollbackRelease()
	{
		$this->task('Rollback')->execute();

		$this->assertEquals(10000000000000, $this->app['rocketeer.releases']->getCurrentRelease());
	}

	public function testCanSetupServer()
	{
		$this->app['files']->deleteDirectory($this->server);
		$this->task('Setup')->execute();

		$this->assertFileExists($this->server);
		$this->assertFileExists($this->server.'/current');
		$this->assertFileExists($this->server.'/releases');
	}

	public function testCanDeployToServer()
	{
		$this->app['config']->shouldReceive('get')->with('rocketeer::scm')->andReturn(array(
			'repository' => 'https://github.com/Anahkiasen/rocketeer.git',
			'username'   => '',
			'password'   => '',
		));

		$this->task('Deploy')->execute();
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
		$this->markTestSkipped('Need to find a solution for this one');

		$release = glob($this->server.'/releases/*');
		$release = basename($release[1]);
		$this->task->releasesManager->updateCurrentRelease($release);

		// Passing tests
		$remote = clone $this->app['remote'];
		$remote->shouldReceive('status')->andReturn(0);
		$this->task->remote = $remote;
		$tests = $this->task->runTests('tests/DeploymentsManagerTest.php');
		$this->assertTrue($tests);

		// Failing tests
		$remote = clone $this->app['remote'];
		$remote->shouldReceive('status')->andReturn(1);
		$this->task->remote = $remote;
		$tests = $this->task->runTests();
		$this->assertFalse($tests);

		$this->app['files']->delete($this->server.'/current');
		system('rm -rf '.$this->server);
	}

	public function testCanUpdateRepository()
	{
		$this->task->runForCurrentRelease('git init');
		$this->task->updateRepository();
		$output = $this->task->run('git status');

		$this->assertContains('working directory clean', $output);
	}

	public function testCanDisplayOutputOfCommandsIfVerbose()
	{
		$task = $this->pretendTask('Check', array(
			'verbose' => true,
			'pretend' => false
		));

		ob_start();
			$task->run('ls');
		$output = ob_get_clean();

		$this->assertContains('tests', $output);
	}

	public function testCanPretendToRunTasks()
	{
		$task = $this->pretendTask();

		$output = $task->run('ls');
		$this->assertEquals('ls', $output);
	}
}
