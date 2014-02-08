<?php
namespace Rocketeer\Traits\BashModules;

use Rocketeer\TestCases\RocketeerTestCase;

class FilesystemTest extends RocketeerTestCase
{
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

	public function testCanListContentsOfAFolder()
	{
		$contents = $this->task->listContents($this->server);

		$this->assertEquals(array('current', 'releases', 'shared', 'state.json'), $contents);
	}

	public function testCanCheckIfFileExists()
	{
		$this->assertTrue($this->task->fileExists($this->server));
		$this->assertFalse($this->task->fileExists($this->server.'/nope'));
	}
}
