<?php
namespace Traits;

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
}
