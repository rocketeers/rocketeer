<?php

class TeardownTest extends RocketeerTests
{
	public function testCanTeardownServer()
	{
		$this->task('Teardown')->execute();

		$this->assertFileNotExists($this->deploymentsFile);
		$this->assertFileNotExists($this->server);
	}

	public function testCanAbortTeardown()
	{
		$command = Mockery::mock('Command');;
		$command->shouldReceive('confirm')->andReturn(false);
		$command->shouldReceive('info')->andReturnUsing(function ($message) { return $message; });

		$message = $this->task('Teardown', $command)->execute();

		$this->assertEquals('Teardown aborted', $message);
	}
}
