<?php
namespace Traits;

use Rocketeer\TestCases\RocketeerTestCase;

class StepsRunnerTest extends RocketeerTestCase
{
	public function testCanRunStepsOnSilentCommands()
	{
		$task = $this->task;
		$task->steps()->copy($this->server.'/state.json', $this->server.'/state2.json');

		$results = $task->runSteps();

		$this->assertTrue($results);
	}
}
