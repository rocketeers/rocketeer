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
			},
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
			},
		);

		$pipeline = $this->queue->run($queue);

		$this->assertTrue($pipeline->succeeded());
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

		$pipeline = $this->queue->run(array(
			'ls -a',
			function () {
				return 'JOEY DOESNT SHARE FOOD';
			},
		));

		$output = array_slice($this->history->getFlattenedOutput(), 2, 3);
		$this->assertTrue($pipeline->succeeded());
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

		$this->queue->on(array('staging', 'production'), function ($task) {
			return $task->connections->getConnection().' - '.$task->connections->getStage();
		});

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
		                   ->shouldReceive('isSupported')->andReturn(true)
		                   ->shouldReceive('values')->once()->with(Mockery::type('array'))
		                   ->mock();

		$this->mockCommand(['parallel' => true]);
		$this->queue->setParallel($parallel);

		$task = function () {
			sleep(1);

			return time();
		};

		$this->queue->execute(array(
			$task,
			$task,
		));
	}

	public function testCanCancelQueueIfTaskFails()
	{
		$this->expectOutputString('The tasks queue was canceled by task "MyCustomHaltingTask"');

		$this->mockCommand([], array(
			'error' => function ($error) {
				echo $error;
			},
		));

		$pipeline = $this->queue->run(array(
			'Rocketeer\Dummies\MyCustomHaltingTask',
			'Rocketeer\Dummies\MyCustomTask',
		));

		$this->assertTrue($pipeline->failed());
		$this->assertEquals([false], $this->history->getFlattenedOutput());
	}

	public function testFallbacksToSynchonousIfErrorWhenRunningParallels()
	{
		$parallel = Mockery::mock('Parallel')
		                   ->shouldReceive('isSupported')->andReturn(true)
		                   ->shouldReceive('values')->once()->andThrow('LogicException')
		                   ->mock();

		$this->mockCommand(['parallel' => true]);
		$this->queue->setParallel($parallel);

		$this->queue->run(['ls']);
	}
}
