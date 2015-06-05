<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

        $this->assertEquals([
            ['run', ['ls']],
        ], $task->steps()->getSteps());
        $task->runSteps();
        $task->steps()->run('php --version');
        $this->assertEquals([
            ['run', ['php --version']],
        ], $task->steps()->getSteps());
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
        $this->expectFiredEvent('tasks.cleanup.foobar.before');
        $this->expectFiredEvent('tasks.cleanup.foobar.after');

        $this->task->steps()->addStepWithEvents('foobar', function () {

        });

        $this->task->runSteps();
    }
}
