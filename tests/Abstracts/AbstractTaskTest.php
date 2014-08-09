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

	public function testCanRunMigrations()
	{
		$task = $this->pretendTask();
		$php  = exec('which php');

		$commands = $task->runMigrations();
		$this->assertEquals($php.' artisan migrate', $commands[1]);

		$commands = $task->runMigrations(true);
		$this->assertEquals($php.' artisan migrate --seed', $commands[1]);
	}

	public function testCanFireEventsDuringTasks()
	{
		$this->expectOutputString('foobar');

		$this->tasksQueue()->listenTo('closure.test.foobar', function () {
			echo 'foobar';
		});

		$this->tasksQueue()->execute(function ($task) {
			$task->fireEvent('test.foobar');
		}, 'staging');
	}

	public function testTaskCancelsIfEventHalts()
	{
		$this->expectOutputString('abc');

		$this->swapConfig(array(
			'rocketeer::hooks' => [],
		));

		$this->tasksQueue()->registerConfiguredEvents();
		$this->tasksQueue()->listenTo('deploy.before', array(
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
}
