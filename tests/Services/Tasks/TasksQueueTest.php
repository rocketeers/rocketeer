<?php
namespace Rocketeer\Services\Tasks;

use Mockery;
use Rocketeer\TestCases\RocketeerTestCase;

class TasksQueueTest extends RocketeerTestCase
{

	public function testCanRunQueue()
	{
		$this->swapConfig(array(
			'rocketeer::default' => 'production',
		));

		$this->expectOutputString('JOEY DOESNT SHARE FOOD');
		$this->queue->run(array(
			function () {
				print 'JOEY DOESNT SHARE FOOD';
			}
		), $this->getCommand());
	}

	public function testCanRunQueueOnDifferentConnectionsAndStages()
	{
		$this->swapConfig(array(
			'rocketeer::default'       => ['staging', 'production'],
			'rocketeer::stages.stages' => ['first', 'second'],
		));

		$output = array();
		$queue  = array(
			function ($task) use (&$output) {
				$output[] = $task->connections->getConnection().' - '.$task->connections->getStage();
			}
		);

		$status = $this->queue->run($queue);

		$this->assertTrue($status);
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

		$status = $this->queue->run(array(
			'ls -a',
			function () {
				return 'JOEY DOESNT SHARE FOOD';
			}
		));

		$output = array_slice($this->history->getFlattenedOutput(), 2, 3);
		$this->assertTrue($status);
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

		$status = $this->queue->on(array('staging', 'production'), function ($task) {
			return $task->connections->getConnection().' - '.$task->connections->getStage();
		});

		$this->assertTrue($status);
		$this->assertEquals(array(
			'staging - first',
			'staging - second',
			'production - first',
			'production - second',
		), $this->history->getFlattenedOutput());
	}

	public function testCanRunTasksInParallel()
	{
		$parallel = Mockery::mock('Parallel')
		                   ->shouldReceive('run')->once()->with(Mockery::type('Illuminate\Support\Collection'))
		                   ->mock();

		$this->mockCommand(['parallel' => true]);
		$this->tasksQueue()->setParallel($parallel);

		$this->tasksQueue()->execute(['ls', 'ls']);
	}

	public function testCanCancelQueueIfTaskFails()
	{
		$this->expectOutputString('The tasks queue was canceled by task "MyCustomHaltingTask"');

		$this->mockCommand([], array(
			'error' => function ($error) {
				echo $error;
			},
		));

		$status = $this->queue->run(array(
			'Rocketeer\Dummies\MyCustomHaltingTask',
			'Rocketeer\Dummies\MyCustomTask',
		));

		$this->assertFalse($status);
		$this->assertEquals([false], $this->history->getFlattenedOutput());
	}
}
