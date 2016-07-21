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

namespace Rocketeer\Tasks;

use Prophecy\Argument;
use Rocketeer\Dummies\Tasks\MyCustomHaltingTask;
use Rocketeer\TestCases\RocketeerTestCase;

class AbstractTaskTest extends RocketeerTestCase
{
    public function testCanDisplayOutputOfCommandsIfVerbose()
    {
        $this->expectOutputRegex('/tests/');

        $this->mockEchoingCommand();
        $this->command->getProphecy()->getVerbosity()->willReturn(999);
//        $this->mockCommand(['verbose' => true], []);
        $task = $this->task('Check');

        $task->run('ls');
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
        $this->disableTestEvents();
        $this->expectFiredEvent('closure.test.foobar');

        $this->queue->execute(function ($task) {
            $task->fireEvent('test.foobar');
        }, 'staging');
    }

    public function testTaskCancelsIfEventHalts()
    {
        $this->expectOutputString('abc');
        $this->disableTestEvents();

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
            function ($task) {
                echo 'c';

                return $task->halt();
            },
            function () {
                echo 'd';
            },
        ]);

        $task = $this->pretendTask('Deploy');
        $results = $task->fireEvent('before');

        $this->assertFalse($results);
    }

    public function testCanListenToSubtasks()
    {
        $this->disableTestEvents();
        $this->tasks->listenTo('dependencies.before', ['ls']);

        $this->pretendTask('Deploy')->fire();

        $history = $this->history->getFlattenedOutput();
        foreach ($history as $entry) {
            if (is_array($entry) && in_array('ls', $entry, true)) {
                break;
            }
        }

        $this->assertHistory([
            'cd {server}/releases/{release}',
            'ls',
        ], $entry);
    }

    public function testDoesntDuplicateQueuesOnSubtasks()
    {
        $this->pretendTask('Deploy')->execute();
        $expected = count($this->history->getFlattenedHistory());
        $this->history->reset();

        $this->swapConfig([
            'default' => ['staging', 'production'],
            'hooks' => [],
        ]);

        $this->pretend();
        $this->queue->run('Deploy');

        $this->assertCount($expected * 2, $this->history->getFlattenedHistory());
    }

    public function testCanHookIntoHaltingEvent()
    {
        $this->expectFiredEvent('deploy.halt');
        $this->tasks->before('deploy', MyCustomHaltingTask::class);

        $this->pretendTask('Deploy')->fire();
    }

    public function testHaltingEventBubblesUp()
    {
        $this->expectFiredEvent('deploy.halt');
        $this->tasks->before('SwapSymlink', function ($task) {
            $task->halt();
        });

        $this->pretendTask('Deploy')->fire();
    }

    public function testHaltingCancelsQueue()
    {
        $this->expectOutputString('');

        $this->queue->run([
            function (AbstractTask $task) {
                $task->halt('foobar');
            },
            function () {
                echo 'foobar';
            },
        ]);
    }

    public function testCanDisplayReleasesTable()
    {
        $headers = ['#', 'Path', 'Deployed at', 'Status'];
        $releases = [
            [0, 20000000000000, '<fg=green>1999-11-30 00:00:00</fg=green>', '✓'],
            [1, 15000000000000, '<fg=red>1499-11-30 00:00:00</fg=red>', '✘'],
            [2, 10000000000000, '<fg=green>0999-11-30 00:00:00</fg=green>', '✓'],
        ];

        $this->command->getProphecy()->table($headers, $releases)->shouldBeCalled()->willReturn(null);

        $this->task('CurrentRelease')->execute();
    }

    public function testCanGetOptionsViaCommandOrSetters()
    {
        $this->mockCommand(['pretend' => true, 'foo' => 'bar']);

        $task = $this->task('Deploy');
        $task->configure(['baz' => 'qux']);

        $this->assertTrue($task->getOption('pretend'));
        $this->assertEquals('bar', $task->getOption('foo', true));
        $this->assertEquals('qux', $task->getOption('baz', true));
    }

    public function testCanSetLocalModeIfOnlyTaskIsLocal()
    {
        $this->pretend();
        $task = $this->builder->buildTask(function (AbstractTask $task) {
            return $task->connections->getCurrentConnectionKey()->toLongHandle();
        });
        $task->setLocal(true);
        $results = $task->fire();

        $this->assertEquals('@local', $results);
    }

    public function testDoesntRunAfterEventIfTaskFailed()
    {
        $this->expectOutputString('');

        $task = $this->builder->buildTask(MyCustomHaltingTask::class);

        $this->tasks->after($task, function () {
            echo 'fired';
        });

        $task->fire();
    }

    /**
     * @dataProvider provideTasks
     *
     * @param string $taskName
     */
    public function testAllTasksCanFireAfterEvent($taskName)
    {
        $this->tasks->clearRegisteredEvents();
        $this->expectFiredEvent('rocketeer.tasks.'.$taskName.'.after');

        $this->mockRemote([
            'php -m' => 'mysql',
            'which composer' => 'composer',
            'which php' => 'php',
            'which phpunit' => 'phpunit',
        ]);

        $prophecy = $this->bindFilesystemProphecy(true);
        $prophecy->read(Argument::cetera())->willReturn();
        $prophecy->delete(Argument::cetera())->willReturn();
        $prophecy->has(Argument::any())->willReturn(true);

        $task = $this->pretendTask($taskName);
        $task->fire();
    }

    public function provideTasks()
    {
        return [
            ['cleanup'],
            ['dependencies'],
            ['deploy'],
            ['migrate'],
            ['rollback'],
            ['teardown'],
            ['test'],
            ['update'],
        ];
    }
}
