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

namespace Rocketeer\Abstracts;

use Rocketeer\TestCases\RocketeerTestCase;

class AbstractTaskTest extends RocketeerTestCase
{
    public function testCanDisplayOutputOfCommandsIfVerbose()
    {
        $task = $this->task('Check', [
            'verbose' => true,
        ]);

        ob_start();
        $task->run('ls');
        $output = ob_get_clean();

        $this->assertContains('tests', $output);
    }

    public function testCanPretendToRunTasks()
    {
        $task = $this->pretendTask();
        $commands = $task->run('ls');

        $this->assertEquals('ls', $commands);
    }

    public function testCanGetDescription()
    {
        $task = $this->task('Setup');

        $this->assertNotNull($task->getDescription());
    }

    public function testCanFireEventsDuringTasks()
    {
        $this->expectOutputString('foobar');
        $this->swapConfig(['rocketeer::hooks' => []]);

        $this->tasks->listenTo('closure.test.foobar', function () {
            echo 'foobar';
        });

        $this->queue->execute(function ($task) {
            $task->fireEvent('test.foobar');
        }, 'staging');
    }

    public function testTaskCancelsIfEventHalts()
    {
        $this->expectOutputString('abc');

        $this->swapConfig([
            'rocketeer::hooks' => [],
        ]);

        $this->tasks->registerConfiguredEvents();
        $this->tasks->listenTo('deploy.before', [
            function () {
                echo 'a';

                return true;
            },
            function () {
                echo 'b';

                return 'lol';
            },
            function () {
                echo 'c';

                return false;
            },
            function () {
                echo 'd';
            },
        ]);

        $task = $this->pretendTask('Deploy');
        $task->fire();
    }

    public function testCanListenToSubtasks()
    {
        $this->swapConfig([
            'rocketeer::hooks' => [],
        ]);

        $this->tasks->listenTo('dependencies.before', ['ls']);

        $this->pretendTask('Deploy')->fire();

        $history = $this->history->getFlattenedOutput();
        $this->assertHistory([
            'cd {server}/releases/{release}',
            'ls',
        ], array_get($history, 3));
    }

    public function testDoesntDuplicateQueuesOnSubtasks()
    {
        $this->swapConfig([
            'rocketeer::default' => ['staging', 'production'],
        ]);

        $this->pretend();
        $this->queue->run('Deploy');

        $this->assertCount(18, $this->history->getFlattenedHistory());
    }

    public function testCanHookIntoHaltingEvent()
    {
        $this->expectOutputString('halted');

        $this->tasks->before('deploy', 'Rocketeer\Dummies\Tasks\MyCustomHaltingTask');

        $this->tasks->listenTo('deploy.halt', function () {
            echo 'halted';
        });

        $this->pretendTask('Deploy')->fire();
    }

    public function testCanDisplayReleasesTable()
    {
        $headers = ['#', 'Path', 'Deployed at', 'Status'];
        $releases = [
            [0, 20000000000000, '<fg=green>1999-11-30 00:00:00</fg=green>', '✓'],
            [1, 15000000000000, '<fg=red>1499-11-30 00:00:00</fg=red>', '✘'],
            [2, 10000000000000, '<fg=green>0999-11-30 00:00:00</fg=green>', '✓'],
        ];

        $this->app['rocketeer.command'] = $this->getCommand()
                                               ->shouldReceive('table')->with($headers, $releases)->andReturn(null)->once()
                                               ->mock();

        $this->task('CurrentRelease')->execute();
    }

    public function testDoesntRunAfterEventIfTaskFailed()
    {
        $this->expectOutputString('');

        $task = 'Rocketeer\Dummies\Tasks\MyCustomHaltingTask';
        $task = $this->builder->buildTask($task);

        $this->tasks->after($task, function () {
            echo 'fired';
        });

        $task->fire();
    }
}
