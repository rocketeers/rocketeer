<?php
namespace Rocketeer\Traits;

use Rocketeer\TestCases\RocketeerTestCase;

class StepsRunnerTest extends RocketeerTestCase
{
	public function testCanRunStepsOnSilentCommands()
	{
		$task = $this->task;
		$copy = $this->server.'/state2.json';
		$task->steps()->copy($this->server.'/state.json', $copy);

		$results = $task->runSteps();

		$this->files->delete($copy);
		$this->assertTrue($results);
	}

	public function testStepsAreClearedOnceRun()
	{
		$task = $this->task;
		$task->steps()->run('ls');

		$this->assertEquals(array(
			['run', ['ls']],
		), $task->steps()->getSteps());
		$task->runSteps();
		$task->steps()->run('php --version');
		$this->assertEquals(array(
			['run', ['php --version']],
		), $task->steps()->getSteps());
	}
}
