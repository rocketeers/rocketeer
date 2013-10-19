<?php
use Rocketeer\Facades\Rocketeer;

class TasksQueueTest extends RocketeerTests
{
	public function testCanUseFacadeOutsideOfLaravel()
	{
		Rocketeer::before('deploy', 'ls');
		$before = Rocketeer::getBefore($this->task('Deploy'));

		$this->assertEquals(array('ls'), $before);
	}

	public function testCanBuildTaskByName()
	{
		$task = $this->tasksQueue()->buildTask('Rocketeer\Tasks\Deploy');

		$this->assertInstanceOf('Rocketeer\Traits\Task', $task);
	}

	public function testCanBuildCustomTaskByName()
	{
		$tasks = $this->tasksQueue()->buildQueue(array('Rocketeer\Tasks\Check'));

		$this->assertInstanceOf('Rocketeer\Tasks\Check', $tasks[0]);
		$this->assertInstanceOf('Tasks\MyCustomTask', $tasks[1]);
	}

	public function testCanAddCommandsToArtisan()
	{
		$command = $this->tasksQueue()->add('Rocketeer\Tasks\Deploy');
		$this->assertInstanceOf('Rocketeer\Commands\BaseTaskCommand', $command);
		$this->assertInstanceOf('Rocketeer\Tasks\Deploy', $command->getTask());
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

		$closureReflection = new ReflectionFunction ($closure);
		$this->assertEquals(array('stringTask' => 'echo "I love ducks"'), $closureReflection->getStaticVariables());

		$this->assertEquals('I love ducks', $string->execute());
	}

	public function testCanBuildTaskFromClosure()
	{
		$originalClosure = function ($task) {
			return $task->getCommand()->info('echo "I love ducks"');
		};

		$closure = $this->tasksQueue()->buildTaskFromClosure($originalClosure);
		$this->assertInstanceOf('Rocketeer\Tasks\Closure', $closure);
		$this->assertEquals($originalClosure, $closure->getClosure());
	}

	public function testCanBuildQueue()
	{
		$queue = array(
			'foobar',
			function ($task) {
				return 'lol';
			},
			'Rocketeer\Tasks\Deploy'
		);

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
		$this->swapConfig(array(
			'rocketeer::connections' => 'production',
		));

		$this->expectOutputString('JOEY DOESNT SHARE FOOD');
		$this->tasksQueue()->run(array(
			function ($task) {
				print 'JOEY DOESNT SHARE FOOD';
			}
		), $this->getCommand());
	}

	public function testCanRunQueueOnDifferentConnectionsAndStages()
	{
		$this->swapConfig(array(
			'rocketeer::connections'   => array('staging', 'production'),
			'rocketeer::stages.stages' => array('first', 'second'),
		));

		$output = array();
		$queue = array(
			function ($task) use (&$output) {
				$output[] = $task->rocketeer->getConnection(). ' - ' .$task->rocketeer->getStage();
			}
		);

		$queue = $this->tasksQueue()->buildQueue($queue);
		$this->tasksQueue()->run($queue, $this->getCommand());

		$this->assertEquals(array(
			'staging - first',
			'staging - second',
			'production - first',
			'production - second',
		), $output);
	}

	public function testCanRunQueueViaExecute()
	{
		$this->swapConfig(array(
			'rocketeer::connections' => 'production',
		));

		$output = $this->tasksQueue()->execute(array(
			'ls -a',
			function ($task) {
				return 'JOEY DOESNT SHARE FOOD';
			}
		));

		$this->assertEquals(array(
			'.'.PHP_EOL.'..'.PHP_EOL.'.gitkeep',
			'JOEY DOESNT SHARE FOOD',
		), $output);
	}

	public function testCanRunOnMultipleConnectionsViaOn()
	{
		$this->swapConfig(array(
			'rocketeer::stages.stages' => array('first', 'second'),
		));

		$output = $this->tasksQueue()->on(array('staging', 'production'), function ($task) {
			return $task->rocketeer->getConnection(). ' - ' .$task->rocketeer->getStage();
		});

		$this->assertEquals(array(
			'staging - first',
			'staging - second',
			'production - first',
			'production - second',
		), $output);
	}
}
