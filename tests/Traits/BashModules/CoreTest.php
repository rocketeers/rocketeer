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
		$timestamp           = $this->task->getTimestamp();

		$this->assertEquals(date('YmdHis'), $timestamp);
	}

	public function testDoesntAppendEnvironmentToStandardTasks()
	{
		$this->app['rocketeer.rocketeer']->setStage('staging');
		$commands = $this->pretendTask()->processCommands(array(
			'artisan something',
			'rm readme*',
		));

		$this->assertEquals(array(
			'artisan something --env="staging"',
			'rm readme*',
		), $commands);
	}

	public function testCanRemoveCommonPollutingOutput()
	{
		$this->app['remote'] = $this->getRemote('stdin: is not a tty'.PHP_EOL.'something');
		$result = $this->app['rocketeer.bash']->run('ls');

		$this->assertEquals('something', $result);
	}
}
