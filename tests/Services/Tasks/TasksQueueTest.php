<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\Tasks;

use Mockery;
use Mockery\MockInterface;
use Rocketeer\Abstracts\AbstractTask;
use Rocketeer\Services\Connections\RemoteHandler;
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
                print 'JOEY DOESNT SHARE FOOD';
            },
        ], $this->getCommand());
    }

    public function testCanRunQueueOnDifferentConnectionsAndStages()
    {
        $this->swapConfig([
            'default'       => ['staging', 'production'],
            'stages.stages' => ['first', 'second'],
        ]);

        $output = [];
        $queue  = [
            function (AbstractTask $task) use (&$output) {
                $connection = $task->connections->getCurrentConnection();
                $output[]   = $connection->name.' - '.$connection->stage;
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

    public function testDoesntSettingStageDefaultsToAll()
    {
        $this->swapConfig([
            'stages.default' => [],
            'stages.stages'  => ['first', 'second'],
        ]);

        $this->assertEquals(['first', 'second'], $this->queue->getStages('production'));
    }

    public function testCanRunTaskOnAllStages()
    {
        $this->mockCommand([
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
            $connection = $task->connections->getCurrentConnection();

            return $connection->name.' - '.$connection->stage;
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

        $this->mock('rocketeer.explainer', 'QueueExplainer', function (MockInterface $mock) {
            return $mock->shouldReceive('error')->andReturnUsing(function ($string) {
                echo $string;
            });
        });

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

    public function testSkipsTasksThatArentFitForConnection()
    {
        $this->pretend();

        $this->app['rocketeer.remote'] = new RemoteHandler($this->app);
        $this->swapConnections([
            'production' => [
                'host'     => 'foobar.com',
                'username' => 'foobar',
                'password' => 'foobar',
                'roles'    => ['foo', 'bar'],
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
}
