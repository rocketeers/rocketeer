<?php
namespace Rocketeer\Services\Tasks;

use Mockery;
use Mockery\MockInterface;
use Rocketeer\Services\Connections\RemoteHandler;
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

    public function testDoesntSettingStageDefaultsToAll()
    {
        $this->swapConfig(array(
            'rocketeer::stages.default' => [],
            'rocketeer::stages.stages'  => ['first', 'second'],
        ));

        $this->assertEquals(['first', 'second'], $this->queue->getStages('production'));
    }

    public function testCanRunTaskOnAllStages()
    {
        $this->mockCommand(array(
            'stage' => 'all',
        ));
        $this->swapConfig(array(
            'rocketeer::stages.stages' => ['first', 'second'],
        ));

        $this->assertEquals(['first', 'second'], $this->queue->getStages('production'));
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

        $output = array_slice($this->history->getFlattenedOutput(), 2, 4);
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

        $this->mock('rocketeer.explainer', 'QueueExplainer', function (MockInterface $mock) {
            return $mock->shouldReceive('error')->andReturnUsing(function ($string) {
                echo $string;
            });
        });

        $pipeline = $this->queue->run(array(
            'Rocketeer\Dummies\Tasks\MyCustomHaltingTask',
            'Rocketeer\Dummies\Tasks\MyCustomTask',
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

    public function testSkipsTasksThatArentFitForConnection()
    {
        $this->pretend();

        $this->app['rocketeer.remote'] = new RemoteHandler($this->app);
        $this->swapConnections(array(
            'production' => array(
                'host'     => 'foobar.com',
                'username' => 'foobar',
                'password' => 'foobar',
                'roles'    => ['foo', 'bar'],
            ),
        ));

        $this->tasks->task('YES', function ($task) {
            $task->run('YES');
        });
        $this->tasks->task('NO', function ($task) {
            $task->run('NO');
        });

        $this->roles->assignTasksRoles(array(
            'baz' => 'NO',
            'foo' => 'YES',
        ));

        $this->queue->run(array(
            'YES',
            'NO',
        ));

        $this->assertHistoryContains('YES');
        $this->assertHistoryNotContains('NO');
    }
}
