<?php
namespace Rocketeer\Abstracts;

use Rocketeer\TestCases\RocketeerTestCase;

class AbstractTaskTest extends RocketeerTestCase
{
    public function testCanDisplayOutputOfCommandsIfVerbose()
    {
        $this->expectOutputRegex('/tests/');

        $this->mockCommand(['verbose' => true], [], true);
        $task = $this->task('Check');

        $task->run('ls');
    }

    public function testCanPretendToRunTasks()
    {
        $task     = $this->pretendTask();
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
        $this->tasks->listenTo('deploy.before', array(
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
        ));

        $task    = $this->pretendTask('Deploy');
        $results = $task->fire();

        $this->assertFalse($results);
    }

    public function testCanListenToSubtasks()
    {
        $this->disableTestEvents();
        $this->tasks->listenTo('dependencies.before', ['ls']);

        $this->pretendTask('Deploy')->fire();

        $history = $this->history->getFlattenedOutput();
        $this->assertHistory(array(
            'cd {server}/releases/{release}',
            'ls',
        ), array_get($history, 4));
    }

    public function testDoesntDuplicateQueuesOnSubtasks()
    {
        $this->swapConfig(array(
            'rocketeer::default' => ['staging', 'production'],
        ));

        $this->pretend();
        $this->queue->run('Deploy');

        $this->assertCount(18, $this->history->getFlattenedHistory());
    }

    public function testCanHookIntoHaltingEvent()
    {
        $this->expectFiredEvent('deploy.halt');
        $this->tasks->before('deploy', 'Rocketeer\Dummies\Tasks\MyCustomHaltingTask');

        $this->pretendTask('Deploy')->fire();
    }

    public function testHaltingCancelsQueue()
    {
        $this->expectOutputString('');

        $this->queue->run(array(
            function (AbstractTask $task) {
                $task->halt('foobar');
            },
            function () {
                echo 'foobar';
            },
        ));
    }

    public function testCanDisplayReleasesTable()
    {
        $headers  = ['#', 'Path', 'Deployed at', 'Status'];
        $releases = array(
            [0, 20000000000000, '<fg=green>1999-11-30 00:00:00</fg=green>', '✓'],
            [1, 15000000000000, '<fg=red>1499-11-30 00:00:00</fg=red>', '✘'],
            [2, 10000000000000, '<fg=green>0999-11-30 00:00:00</fg=green>', '✓'],
        );

        $this->app['rocketeer.command'] = $this->getCommand()
                                               ->shouldReceive('table')->with($headers, $releases)->andReturn(null)->once()
                                               ->mock();

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
            return $task->connections->getCurrent()->toLongHandle();
        });
        $task->setLocal(true);
        $results = $task->fire();

        $this->assertEquals('anahkiasen@local', $results);
    }
}
