<?php
namespace Rocketeer\Abstracts;

use Rocketeer\TestCases\RocketeerTestCase;

class AbstractTaskTest extends RocketeerTestCase
{
	public function testCanDisplayOutputOfCommandsIfVerbose()
	{
		$task = $this->task('Check', array(
			'verbose' => true,
		));

		ob_start();
		$task->run('ls');
		$output = ob_get_clean();

		$this->assertContains('tests', $output);
	}

	public function testCanPretendToRunTasks()
	{
		$task     = $this->pretendTask();
		$commands = $task->run('ls');

		$this->assertEquals('ls', $commands);
	}

	public function testCanGetDescription()
	{
		$task = $this->task('Setup');

		$this->assertNotNull($task->getDescription());
	}

	public function testCanFireEventsDuringTasks()
	{
		$this->expectOutputString('foobar');
		$this->swapConfig(['rocketeer::hooks' => []]);

		$this->tasks->listenTo('closure.test.foobar', function () {
			echo 'foobar';
		});

		$this->queue->execute(function ($task) {
			$task->fireEvent('test.foobar');
		}, 'staging');
	}

	public function testTaskCancelsIfEventHalts()
	{
		$this->expectOutputString('abc');

		$this->swapConfig(array(
			'rocketeer::hooks' => [],
		));

		$this->tasks->registerConfiguredEvents();
		$this->tasks->listenTo('deploy.before', array(
			function () {
				echo 'a';

				return true;
			},
			function () {
				echo 'b';

				return 'lol';
			},
			function () {
				echo 'c';

				return false;
			},
			function () {
				echo 'd';
			},
		));

		$task = $this->pretendTask('Deploy');
		$task->fire();
	}

	public function testCanListenToSubtasks()
	{
		$this->swapConfig(array(
			'rocketeer::hooks' => [],
		));

		$this->tasks->listenTo('dependencies.before', ['ls']);

		$this->pretendTask('Deploy')->fire();

		$history = $this->history->getFlattenedOutput();
		$this->assertHistory(array(
			'cd {server}/releases/{release}',
			'ls',
		), $history[3]);
	}

	public function testDoesntDuplicateQueuesOnSubtasks()
	{
		$this->swapConfig(array(
			'rocketeer::default' => ['staging', 'production'],
		));

		$this->pretend();
		$this->queue->run('Deploy');

		$this->assertCount(24, $this->history->getFlattenedHistory());
	}

	public function testCanHookIntoHaltingEvent()
	{
		$this->expectOutputString('halted');

		$this->tasks->before('deploy', 'Rocketeer\Dummies\MyCustomHaltingTask');

		$this->tasks->listenTo('deploy.halt', function () {
			echo 'halted';
		});

		$this->pretendTask('Deploy')->fire();
	}
}
