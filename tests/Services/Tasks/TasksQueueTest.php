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

use KzykHys\Parallel\Parallel;
use LogicException;
use Prophecy\Argument;
use Rocketeer\Dummies\Tasks\MyCustomHaltingTask;
use Rocketeer\Dummies\Tasks\MyCustomTask;
use Rocketeer\Services\Connections\ConnectionsFactory;
use Rocketeer\Services\Display\QueueExplainer;
use Rocketeer\Tasks\AbstractTask;
use Rocketeer\TestCases\RocketeerTestCase;

class TasksQueueTest extends RocketeerTestCase
{
    public function testCanRunQueue()
    {
        $this->swapConfig([
            'default' => 'production',
        ]);

        $this->expectOutputString('JOEY DOESNT SHARE FOOD');
        $this->queue->run([
            function () {
                echo 'JOEY DOESNT SHARE FOOD';
            },
        ], $this->command);
    }

    public function testCanRunQueueOnDifferentConnectionsAndStages()
    {
        $this->swapConfig([
            'default' => ['staging', 'production'],
            'stages.stages' => ['first', 'second'],
            'connections' => [
                'production' => [
                    'servers' => [
                        ['host' => 'a.com'],
                        ['host' => 'b.com'],
                    ],
                ],
                'staging' => [
                    'servers' => [
                        ['host' => 'a.com'],
                        ['host' => 'b.com'],
                    ],
                ],
            ],
        ]);

        $output = [];
        $queue = [
            function (AbstractTask $task) use (&$output) {
                $output[] = $task->connections->getCurrentConnectionKey()->toHandle();
            },
        ];

        $pipeline = $this->queue->run($queue);

        $this->assertCount(8, $pipeline);
        $this->assertTrue($pipeline->succeeded());
        $this->assertEquals([
            'production/a.com/first',
            'production/a.com/second',
            'production/b.com/first',
            'production/b.com/second',
            'staging/a.com/first',
            'staging/a.com/second',
            'staging/b.com/first',
            'staging/b.com/second',
        ], $output);
    }

    public function testDoesntSettingStageDefaultsToAll()
    {
        $this->swapConfig([
            'stages.default' => [],
            'stages.stages' => ['first', 'second'],
        ]);

        $this->assertEquals(['first', 'second'], $this->queue->getStages('production'));
    }

    public function testCanRunTaskOnAllStages()
    {
        $this->bindDummyCommand([
            'stage' => 'all',
        ]);
        $this->swapConfig([
            'stages.stages' => ['first', 'second'],
        ]);

        $this->assertEquals(['first', 'second'], $this->queue->getStages('production'));
    }

    public function testCanRunQueueViaExecute()
    {
        $this->swapConfig([
            'default' => 'production',
        ]);

        $pipeline = $this->queue->run([
            'ls -a',
            function () {
                return 'JOEY DOESNT SHARE FOOD';
            },
        ]);

        $output = array_slice($this->history->getFlattenedOutput(), 1, 2);
        $this->assertTrue($pipeline->succeeded());
        $this->assertEquals([
            '.'.PHP_EOL.'..'.PHP_EOL.'.gitkeep',
            'JOEY DOESNT SHARE FOOD',
        ], $output);
    }

    public function testCanRunOnMultipleConnectionsViaOn()
    {
        $this->swapConfig([
            'stages.stages' => ['first', 'second'],
        ]);

        $this->queue->on(['staging', 'production'], function (AbstractTask $task) {
            $connection = $task->connections->getCurrentConnectionKey();

            return $connection->name.' - '.$connection->stage;
        });

        $this->assertEquals([
            'production - first',
            'production - second',
            'staging - first',
            'staging - second',
        ], $this->history->getFlattenedOutput());
    }

    public function testCanRunTasksInParallel()
    {
        $parallel = $this->prophesize(Parallel::class);
        $parallel->isSupported()->willReturn(true);
        $parallel->values(Argument::type('array'))->shouldBeCalled();

        $this->bindDummyCommand(['--parallel' => true]);
        $this->queue->setParallel($parallel->reveal());

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
        $prophecy = $this->bindProphecy(QueueExplainer::class);
        $pipeline = $this->queue->run([
            MyCustomHaltingTask::class,
            MyCustomTask::class,
        ]);

        $this->assertTrue($pipeline->failed());
        $this->assertEquals([false], $this->history->getFlattenedOutput());
        $prophecy->error('The tasks queue was canceled by task "MyCustomHaltingTask"')->shouldHaveBeenCalled();
    }

    public function testFallbacksToSynchonousIfErrorWhenRunningParallels()
    {
        /** @var Parallel $parallel */
        $parallel = $this->prophesize(Parallel::class);
        $parallel->isSupported()->willReturn(true);
        $parallel->values(Argument::type('array'))->shouldBeCalled()->willThrow(LogicException::class);

        $this->bindDummyCommand(['--parallel' => true]);
        $this->queue->setParallel($parallel->reveal());

        $this->queue->run(['ls']);
    }

    public function testSkipsTasksThatArentFitForConnection()
    {
        $this->pretend();

        $this->container->add(ConnectionsFactory::class, new ConnectionsFactory($this->container));
        $this->swapConnections([
            'production' => [
                'host' => 'foobar.com',
                'username' => 'foobar',
                'password' => 'foobar',
                'roles' => ['foo', 'bar'],
            ],
        ]);

        $this->tasks->task('YES', function ($task) {
            $task->run('YES');
        });
        $this->tasks->task('NO', function ($task) {
            $task->run('NO');
        });

        $this->roles->assignTasksRoles([
            'baz' => 'NO',
            'foo' => 'YES',
        ]);

        $this->queue->run([
            'YES',
            'NO',
        ]);

        $this->assertHistoryContains('YES');
        $this->assertHistoryNotContains('NO');
    }

    public function testCanConvertQueueToArray()
    {
        $task = $this->builder->buildTask(MyCustomTask::class);
        $queue = $this->queue->run($task);

        $this->assertCount(1, $queue);
    }
}
