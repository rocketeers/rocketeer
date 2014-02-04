<?php
namespace Rocketeer\Traits;

use Rocketeer\TestCases\RocketeerTestCase;

class TaskTest extends RocketeerTestCase
{
	public function testCanUpdateRepository()
	{
		$task = $this->pretendTask('Deploy');
		$task->updateRepository();

		$matcher = array(
			array(
				"cd $this->server/releases/20000000000000",
				"git reset --hard",
				"git pull",
			),
		);

		$this->assertEquals($matcher, $task->getHistory());
	}

	public function testCanDisplayOutputOfCommandsIfVerbose()
	{
		$task = $this->pretendTask('Check', array(
			'verbose' => true,
			'pretend' => false
		));

		ob_start();
			$task->run('ls');
		$output = ob_get_clean();

		$this->assertContains('tests', $output);
	}

	public function testCanPretendToRunTasks()
	{
		$task = $this->pretendTask();

		$output = $task->run('ls');
		$this->assertEquals('ls', $output);
	}

	public function testCanGetDescription()
	{
		$task = $this->task('Setup');

		$this->assertNotNull($task->getDescription());
	}

	public function testCanRunMigrations()
	{
		$task = $this->pretendTask();
		$php = exec('which php');

		$commands = $task->runMigrations();
		$this->assertEquals($php.' artisan migrate', $commands[1]);

		$commands = $task->runMigrations(true);
		$this->assertEquals($php.' artisan migrate --seed', $commands[1]);
	}

	public function testCanFireEventsDuringTasks()
	{
		$this->expectOutputString('foobar');

		$this->tasksQueue()->listenTo('closure.test.foobar', function ($task) {
			echo 'foobar';
		});

		$task = $this->tasksQueue()->execute(function ($task) {
			$task->fireEvent('test.foobar');
		}, 'staging');
	}
}
