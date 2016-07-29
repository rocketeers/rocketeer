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

namespace Rocketeer\Traits;

use Rocketeer\Dummies\DummyWithSteps;
use Rocketeer\TestCases\BaseTestCase;

class StepsRunnerTest extends BaseTestCase
{
    /**
     * @var DummyWithSteps
     */
    protected $dummy;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->dummy = new DummyWithSteps();
    }

    public function testCanRunStepsOnSilentCommands()
    {
        $copy = $this->server.'/state2.json';
        $this->dummy->steps()->copy($this->server.'/state.json', $copy);

        $results = $this->dummy->runSteps();

        $this->assertTrue($results);
        unlink($copy);
    }

    public function testStepsAreClearedOnceRun()
    {
        $this->dummy->steps()->run('ls');

        $this->assertEquals([
            ['run', ['ls']],
        ], $this->dummy->steps()->getSteps());
        $this->dummy->runSteps();
        $this->dummy->steps()->run('php --version');
        $this->assertEquals([
            ['run', ['php --version']],
        ], $this->dummy->steps()->getSteps());
    }

    public function testCanRunClosures()
    {
        $this->expectOutputString('foobar');

        $this->dummy->steps()->addStep(function ($argument) {
            echo $argument;
        }, 'foobar');

        $this->dummy->runSteps();
    }

    public function testStopsOnStrictFalse()
    {
        $this->expectOutputString('');

        $this->dummy->steps()->addStep(function () {
            return false;
        });
        $this->dummy->steps()->addStep(function () {
            echo 'foobar';

            return true;
        });

        $this->dummy->runSteps();
    }

    public function testCanFireEventAroundStep()
    {
        $this->expectOutputString('foobar.beforefoobar.after');

        $this->dummy->steps()->addStepWithEvents('foobar', function () {
        });

        $this->dummy->runSteps();
    }
}
