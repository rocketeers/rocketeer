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

use ReflectionFunction;
use Rocketeer\TestCases\RocketeerTestCase;

class TasksBuilderTest extends RocketeerTestCase
{
    public function testCanBuildTaskByName()
    {
        $task = $this->builder->buildTaskFromClass('Rocketeer\Tasks\Deploy');

        $this->assertInstanceOf('Rocketeer\Abstracts\AbstractTask', $task);
    }

    public function testCanBuildCustomTaskByName()
    {
        $tasks = $this->builder->buildTasks(['Rocketeer\Tasks\Check']);

        $this->assertInstanceOf('Rocketeer\Tasks\Check', $tasks[0]);
    }

    public function testCanBuildTaskFromString()
    {
        $string = 'echo "I love ducks"';

        $string = $this->builder->buildTaskFromString($string);
        $this->assertInstanceOf('Rocketeer\Tasks\Closure', $string);

        $closure = $string->getClosure();
        $this->assertInstanceOf('Closure', $closure);

        $closureReflection = new ReflectionFunction($closure);
        $this->assertEquals(['stringTask' => 'echo "I love ducks"'], $closureReflection->getStaticVariables());

        $this->assertEquals('I love ducks', $string->execute());
    }

    public function testCanBuildTaskFromClosure()
    {
        $originalClosure = function ($task) {
            return $task->getCommand()->info('echo "I love ducks"');
        };

        $closure = $this->builder->buildTaskFromClosure($originalClosure);
        $this->assertInstanceOf('Rocketeer\Tasks\Closure', $closure);
        $this->assertEquals($originalClosure, $closure->getClosure());
    }

    public function testCanBuildTasks()
    {
        $queue = [
            'foobar',
            function () {
                return 'lol';
            },
            'Rocketeer\Tasks\Deploy',
        ];

        $queue = $this->builder->buildTasks($queue);

        $this->assertInstanceOf('Rocketeer\Tasks\Closure', $queue[0]);
        $this->assertInstanceOf('Rocketeer\Tasks\Closure', $queue[1]);
        $this->assertInstanceOf('Rocketeer\Tasks\Deploy', $queue[2]);
    }

    public function testThrowsExceptionOnUnbuildableTask()
    {
        $this->setExpectedException('Rocketeer\Exceptions\TaskCompositionException');

        $this->builder->buildTaskFromClass('Nope');
    }

    public function testCanCreateCommandOfTask()
    {
        $command = $this->builder->buildCommand('Rocketeer', '');
        $this->assertInstanceOf('Rocketeer\Console\Commands\RocketeerCommand', $command);
        $this->assertEquals('deploy', $command->getName());

        $command = $this->builder->buildCommand('Deploy', 'lol');
        $this->assertInstanceOf('Rocketeer\Console\Commands\DeployCommand', $command);
        $this->assertEquals('deploy:deploy', $command->getName());

        $command = $this->builder->buildCommand('ls', 'ls');
        $this->assertInstanceOf('Rocketeer\Console\Commands\BaseTaskCommand', $command);
        $this->assertEquals('deploy:ls', $command->getName());
    }
}
