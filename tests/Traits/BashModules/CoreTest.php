<?php
namespace Rocketeer\Traits\BashModules;

use Rocketeer\TestCases\RocketeerTestCase;

class CoreTest extends RocketeerTestCase
{
	public function testCanGetArraysFromRawCommands()
	{
		$contents = $this->task->runRaw('ls', true, true);

		$this->assertCount(11, $contents);
	}

	public function testCanCheckStatusOfACommand()
	{
		$this->expectOutputRegex('/.+An error occured: "Oh noes", while running:\ngit clone.+/');

		$this->app['rocketeer.remote'] = clone $this->getRemote()->shouldReceive('status')->andReturn(1)->mock();
		$this->mockCommand([], array(
			'line' => function ($error) {
				echo $error;
			},
		));

		$status = $this->task('Deploy')->checkStatus('Oh noes', 'git clone');

		$this->assertFalse($status);
	}

	public function testCanGetTimestampOffServer()
	{
		$timestamp = $this->task->getTimestamp();

		$this->assertEquals(date('YmdHis'), $timestamp);
	}

	public function testCanGetLocalTimestampIfError()
	{
		$this->mockRemote('NOPE');
		$timestamp = $this->task->getTimestamp();

		$this->assertEquals(date('YmdHis'), $timestamp);
	}

	public function testDoesntAppendEnvironmentToStandardTasks()
	{
		$this->connections->setStage('staging');
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
		$this->mockRemote('stdin: is not a tty'.PHP_EOL.'something');
		$result = $this->bash->run('ls');

		$this->assertEquals('something', $result);
	}

	public function testCanRunCommandsLocally()
	{
		$this->mock('rocketeer.remote', 'Remote', function ($mock) {
			return $mock->shouldReceive('run')->never();
		});

		$this->task->setLocal(true);
		$contents = $this->task->runRaw('ls', true, true);

		$this->assertCount(11, $contents);
	}
}
