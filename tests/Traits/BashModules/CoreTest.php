<?php
namespace Rocketeer\Traits\BashModules;

use Rocketeer\TestCases\RocketeerTestCase;

class CoreTest extends RocketeerTestCase
{
	public function testCanGetArraysFromRawCommands()
	{
		$contents = $this->task->runRaw('ls', true, true);

		$this->assertCount(12, $contents);
	}

	public function testCanCheckStatusOfACommand()
	{
		$this->task->remote = clone $this->getRemote()->shouldReceive('status')->andReturn(1)->mock();
		ob_start();
			$status = $this->task->checkStatus(null, 'error');
		$output = ob_get_clean();
		$this->assertEquals('error'.PHP_EOL, $output);
		$this->assertFalse($status);
	}

	public function testCanGetTimestampOffServer()
	{
		$timestamp = $this->task->getTimestamp();
		$this->assertEquals(date('YmdHis'), $timestamp);
	}

	public function testCanGetLocalTimestampIfError()
	{
		$this->app['remote'] = $this->getRemote('NOPE');
		$timestamp = $this->task->getTimestamp();

		$this->assertEquals(date('YmdHis'), $timestamp);
	}
}
