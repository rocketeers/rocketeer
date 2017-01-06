<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Services\Tasks;

use Mockery;
use Rocketeer\TestCases\RocketeerTestCase;

class TasksQueueTest extends RocketeerTestCase
{
    public function testCanRunQueue()
    {
        $this->swapConfig([
            'rocketeer::default' => 'production',
        ]);

        $this->expectOutputString('JOEY DOESNT SHARE FOOD');
        $this->queue->run([
            function () {
                echo 'JOEY DOESNT SHARE FOOD';
            },
        ], $this->getCommand());
    }

    public function testCanRunQueueOnDifferentConnectionsAndStages()
    {
        $this->swapConfig([
            'rocketeer::default' => ['staging', 'production'],
            'rocketeer::stages.stages' => ['first', 'second'],
        ]);

        $output = [];
        $queue = [
            function ($task) use (&$output) {
                $output[] = $task->connections->getConnection().' - '.$task->connections->getStage();
            },
        ];

        $pipeline = $this->queue->run($queue);

        $this->assertTrue($pipeline->succeeded());
        $this->assertEquals([
            'staging - first',
            'staging - second',
            'production - first',
            'production - second',
        ], $output);
    }

    public function testCanRunQueueViaExecute()
    {
        $this->swapConfig([
            'rocketeer::default' => 'production',
        ]);

        $pipeline = $this->queue->run([
            'ls -a',
            function () {
                return 'JOEY DOESNT SHARE FOOD';
            },
        ]);

        $output = array_slice($this->history->getFlattenedOutput(), 2, 4);
        $this->assertTrue($pipeline->succeeded());
        $this->assertEquals([
            '.'.PHP_EOL.'..'.PHP_EOL.'.gitkeep',
            'JOEY DOESNT SHARE FOOD',
        ], $output);
    }

    public function testCanRunOnMultipleConnectionsViaOn()
    {
        $this->swapConfig([
            'rocketeer::stages.stages' => ['first', 'second'],
        ]);

        $this->queue->on(['staging', 'production'], function ($task) {
            return $task->connections->getConnection().' - '.$task->connections->getStage();
        });

        $this->assertEquals([
            'staging - first',
            'staging - second',
            'production - first',
            'production - second',
        ], $this->history->getFlattenedOutput());
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

        $this->queue->execute([
            $task,
            $task,
        ]);
    }

    public function testCanCancelQueueIfTaskFails()
    {
        $this->expectOutputString('The tasks queue was canceled by task "MyCustomHaltingTask"');

        $this->mockCommand([], [
            'error' => function ($error) {
                echo $error;
            },
        ]);

        $pipeline = $this->queue->run([
            'Rocketeer\Dummies\Tasks\MyCustomHaltingTask',
            'Rocketeer\Dummies\Tasks\MyCustomTask',
        ]);

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
