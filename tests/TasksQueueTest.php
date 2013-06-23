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
		$task   = $this->tasksQueue()->buildTask('Rocketeer\Tasks\Deploy');
		$before = $this->tasksQueue()->getBefore($task);

		$this->assertEquals(array('before', 'foobar'), $before);
	}

	public function testCanGetBeforeOrAfterAnotherTaskBySlug()
	{
		$task   = $this->tasksQueue()->buildTask('Rocketeer\Tasks\Deploy');
		$after  = $this->tasksQueue()->getAfter($task);

		$this->assertEquals(array('after', 'foobar'), $after);
	}

	public function testCanBuildTaskFromString()
	{
		$string = 'I love ducks';

		$string = $this->tasksQueue()->buildTaskFromClosure($string);
		$this->assertInstanceOf('Rocketeer\Tasks\Closure', $string);

		$closure = $string->getClosure();
		$this->assertInstanceOf('Closure', $closure);

		$closureReflection = new ReflectionFunction($closure);
		$this->assertEquals(array('stringTask' => 'I love ducks'), $closureReflection->getStaticVariables());

		// This is a weird test but it makes sense. Trust me I'm, well, not an engineer
		$this->assertEquals('1000000000'.PHP_EOL.'2000000000', $string->execute());
	}

	public function testCanBuildTaskFromClosure()
	{
		$originalClosure = function($task) {
			return $task->getCommand()->info('I love ducks');
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

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get TasksQueue instance
	 *
	 * @return TasksQueue
	 */
	protected function tasksQueue()
	{
		return $this->app['rocketeer.tasks'];
	}

}