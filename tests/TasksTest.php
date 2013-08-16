<?php

class TasksTest extends RocketeerTests
{
	public function testCanUpdateRepository()
	{
		$this->task->runForCurrentRelease('git init');
		$this->task->updateRepository();
		$output = $this->task->run('git status');

		$this->assertContains('working directory clean', $output);
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

		$commands = $task->runMigrations();
		$this->assertEquals('php artisan migrate', $commands[1]);

		$commands = $task->runMigrations(true);
		$this->assertEquals('php artisan migrate --seed', $commands[1]);
	}
}
