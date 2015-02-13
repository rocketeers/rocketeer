<?php
namespace Rocketeer\Traits;

use Rocketeer\TestCases\RocketeerTestCase;

class StepsRunnerTest extends RocketeerTestCase
{
    public function testCanRunStepsOnSilentCommands()
    {
        $task = $this->task;
        $copy = $this->server.'/state2.json';
        $task->steps()->copy($this->server.'/state.json', $copy);

        $results = $task->runSteps();

        $this->files->delete($copy);
        $this->assertTrue($results);
    }

    public function testStepsAreClearedOnceRun()
    {
        $task = $this->task;
        $task->steps()->run('ls');

        $this->assertEquals(array(
            ['run', ['ls']],
        ), $task->steps()->getSteps());
        $task->runSteps();
        $task->steps()->run('php --version');
        $this->assertEquals(array(
            ['run', ['php --version']],
        ), $task->steps()->getSteps());
    }

    public function testCanRunClosures()
    {
        $this->expectOutputString('foobar');

        $this->task->steps()->addStep(function ($argument) {
            echo $argument;
        }, 'foobar');

        $this->task->runSteps();
    }

    public function testStopsOnStrictFalse()
    {
        $this->expectOutputString('');

        $this->task->steps()->addStep(function () {
            return false;
        });
        $this->task->steps()->addStep(function () {
            echo 'foobar';

            return true;
        });

        $this->task->runSteps();
    }

    public function testCanFireEventAroundStep()
    {
        $this->expectOutputString('abc');

        $this->tasks->listenTo('tasks.cleanup.foobar.before', function () {
            echo 'a';
        });

        $this->tasks->listenTo('tasks.cleanup.foobar.after', function () {
           echo 'c';
        });

        $this->task->steps()->addStepWithEvents('foobar', function() {
           echo 'b';
        });

        $this->task->runSteps();
    }
}
