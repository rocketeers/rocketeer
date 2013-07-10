<?php
class BashTest extends RocketeerTests
{
	public function testCanGetBinaryWithFallback()
	{
		$grep = $this->task->which('grep');
		$this->assertTrue(in_array($grep, array('/bin/grep', '/usr/bin/grep')));

		$grep = $this->task->which('grsdg', '/usr/bin/grep');
		$this->assertEquals('/usr/bin/grep', $grep);

		$this->assertFalse($this->task->which('fdsf'));
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
}
