<?php
class BashTest extends RocketeerTests
{
	public function testCanSetCustomPathsForBinaries()
	{
		$this->app['rocketeer.server']->setValue('paths.composer', 'foobar');

		$this->assertEquals('foobar', $this->task->which('composer'));
	}

	public function testCanGetBinary()
	{
		$whichGrep = exec('which grep');
		$grep = $this->task->which('grep');

		$this->assertEquals($whichGrep, $grep);
	}

	public function testCanGetFallbackForBinary()
	{
		$whichGrep = exec('which grep');
		$grep = $this->task->which('foobar', $whichGrep);

		$this->assertEquals($whichGrep, $grep);
		$this->assertFalse($this->task->which('fdsf'));
	}

	public function testCanGetArraysFromRawCommands()
	{
		$contents = $this->task->runRaw('ls', true);

		$this->assertCount(12, $contents);
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

	public function testCanCheckStatusOfACommand()
	{
		$this->task->remote = clone $this->getRemote()->shouldReceive('status')->andReturn(1)->mock();
		ob_start();
			$status = $this->task->checkStatus(null, 'error');
		$output = ob_get_clean();
		$this->assertEquals('error'.PHP_EOL, $output);
		$this->assertFalse($status);

		$this->task->remote = clone $this->getRemote()->shouldReceive('status')->andReturn(0)->mock();
		$status = $this->task->checkStatus(null);
		$this->assertNull($status);
	}

	public function testCanForgetCredentialsIfInvalid()
	{
		$this->app['rocketeer.server']->setValue('credentials', array(
			'repository' => 'https://Anahkiasen@bitbucket.org/Anahkiasen/registry.git',
			'username'   => 'Anahkiasen',
			'password'   => 'baz',
		));

		// Create fake remote
		$remote = clone $this->getRemote();
		$remote->shouldReceive('status')->andReturn(1);
		$task = $this->task();
		$task->remote = $remote;

		$task->cloneRepository($this->server.'/test');
		$this->assertNull($this->app['rocketeer.server']->getValue('credentials'));
	}

	public function testCancelsSymlinkForUnexistingFolders()
	{
		$task    = $this->pretendTask();
		$folder  = '{path.storage}/logs';
		$share   = $task->share($folder);

		$this->assertFalse($share);
	}

	public function testCanSymlinkFolders()
	{
		// Create dummy file
		$folder = $this->server.'/releases/20000000000000/src';
		mkdir($folder);
		file_put_contents($folder.'/foobar.txt', 'test');

		$task    = $this->pretendTask();
		$folder  = '{path.base}/foobar.txt';
		$share   = $task->share($folder);
		$matcher = sprintf('ln -s %s %s', $this->server.'/shared//src/foobar.txt', $this->server.'/releases/20000000000000//src/foobar.txt');

		$this->assertEquals($matcher, $share);
	}
}
