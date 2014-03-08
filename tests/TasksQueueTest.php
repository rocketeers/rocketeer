<?php
namespace Rocketeer;

use ReflectionFunction;
use Rocketeer\Facades\Rocketeer;
use Rocketeer\TestCases\RocketeerTestCase;

class TasksQueueTest extends RocketeerTestCase
{

	public function testCanBuildTaskByName()
	{
		$task = $this->tasksQueue()->buildTaskFromClass('Rocketeer\Tasks\Deploy');

		$this->assertInstanceOf('Rocketeer\Traits\Task', $task);
	}

	public function testCanBuildCustomTaskByName()
	{
		$tasks = $this->tasksQueue()->buildQueue(array('Rocketeer\Tasks\Check'));

		$this->assertInstanceOf('Rocketeer\Tasks\Check', $tasks[0]);
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
		$this->assertInstanceOf('Rocketeer\Tasks\Deploy',  $queue[2]);
	}

	public function testCanRunQueue()
	{
		$this->swapConfig(array(
			'rocketeer::default' => 'production',
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
			'rocketeer::default'       => array('staging', 'production'),
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
			'rocketeer::default' => 'production',
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
