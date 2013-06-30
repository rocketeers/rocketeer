<?php
class TasksQueueTest extends RocketeerTests
{

	public function testCanBuildTaskByName()
	{
		$task = $this->tasksQueue()->buildTask('Rocketeer\Tasks\Deploy');

		$this->assertInstanceOf('Rocketeer\Tasks\Task', $task);
	}

	public function testCanGetTasksBeforeOrAfterAnotherTask()
	{
		$task   = $this->task('Deploy');
		$before = $this->tasksQueue()->getBefore($task);

		$this->assertEquals(array('before', 'foobar'), $before);
	}

	public function testCanAddTasksViaFacade()
	{
		$task   = $this->task('Deploy');
		$before = $this->tasksQueue()->getBefore($task);

		$this->tasksQueue()->before('deploy', 'composer install');

		$newBefore = array_merge($before, array('composer install'));
		$this->assertEquals($newBefore, $this->tasksQueue()->getBefore($task));
	}

	public function testCanAddMultipleTasksViaFacade()
	{
		$task   = $this->task('Deploy');
		$after = $this->tasksQueue()->getAfter($task);

		$this->tasksQueue()->after('Rocketeer\Tasks\Deploy', array(
			'composer install',
			'bower install'
		));

		$newAfter = array_merge($after, array('composer install', 'bower install'));
		$this->assertEquals($newAfter, $this->tasksQueue()->getAfter($task));
	}

	public function testCanAddSurroundTasksToNonExistingTasks()
	{
		$task   = $this->task('Setup');
		$this->tasksQueue()->after('setup', 'composer install');

		$after = array('composer install');
		$this->assertEquals($after, $this->tasksQueue()->getAfter($task));
	}

	public function testCanAddSurroundTasksToMultipleTasks()
	{
		$this->tasksQueue()->after(array('cleanup', 'setup'), 'composer install');

		$after = array('composer install');
		$this->assertEquals($after, $this->tasksQueue()->getAfter($this->task('Setup')));
		$this->assertEquals($after, $this->tasksQueue()->getAfter($this->task('Cleanup')));
	}

	public function testCanGetBeforeOrAfterAnotherTaskBySlug()
	{
		$task   = $this->task('Deploy');
		$after  = $this->tasksQueue()->getAfter($task);

		$this->assertEquals(array('after', 'foobar'), $after);
	}

	public function testCanBuildTaskFromString()
	{
		$string = 'echo "I love ducks"';

		$string = $this->tasksQueue()->buildTaskFromClosure($string);
		$this->assertInstanceOf('Rocketeer\Tasks\Closure', $string);

		$closure = $string->getClosure();
		$this->assertInstanceOf('Closure', $closure);

		$closureReflection = new ReflectionFunction($closure);
		$this->assertEquals(array('stringTask' => 'echo "I love ducks"'), $closureReflection->getStaticVariables());

		$this->assertEquals('I love ducks', $string->execute());
	}

	public function testCanBuildTaskFromClosure()
	{
		$originalClosure = function($task) {
			return $task->getCommand()->info('echo "I love ducks"');
		};

		$closure = $this->tasksQueue()->buildTaskFromClosure($originalClosure);
		$this->assertInstanceOf('Rocketeer\Tasks\Closure', $closure);
		$this->assertEquals($originalClosure, $closure->getClosure());
	}

	public function testCanBuildQueue()
	{
		$queue = array('foobar', function($task) { return 'lol'; }, 'Rocketeer\Tasks\Deploy');
		$queue = $this->tasksQueue()->buildQueue($queue);

		$this->assertInstanceOf('Rocketeer\Tasks\Closure', $queue[0]);
		$this->assertInstanceOf('Rocketeer\Tasks\Closure', $queue[1]);
		$this->assertInstanceOf('Rocketeer\Tasks\Closure', $queue[2]);
		$this->assertInstanceOf('Rocketeer\Tasks\Closure', $queue[3]);
		$this->assertInstanceOf('Rocketeer\Tasks\Deploy',  $queue[4]);
		$this->assertInstanceOf('Rocketeer\Tasks\Closure', $queue[5]);
		$this->assertInstanceOf('Rocketeer\Tasks\Closure', $queue[6]);
	}

	public function testCanRunQueue()
	{
    $this->expectOutputString('JOEY DOESNT SHARE FOOD');
		$this->tasksQueue()->run(array(
			function($task) {
				print 'JOEY DOESNT SHARE FOOD';
			}
		));
	}

}
