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

namespace Rocketeer\Services\Builders\Modules;

use ReflectionFunction;
use Rocketeer\Console\Commands\FlushCommand;
use Rocketeer\Dummies\Tasks\CallableTask;
use Rocketeer\Dummies\Tasks\DummyCoordinatedTask;
use Rocketeer\Dummies\Tasks\DummyShelledTask;
use Rocketeer\Services\Builders\TaskCompositionException;
use Rocketeer\Tasks\AbstractTask;
use Rocketeer\Tasks\Check;
use Rocketeer\Tasks\Closure;
use Rocketeer\Tasks\Deploy;
use Rocketeer\TestCases\RocketeerTestCase;

class TasksBuilderTest extends RocketeerTestCase
{
    public function testCanBuildTaskByName()
    {
        $task = $this->builder->buildTaskFromClass(Deploy::class);

        $this->assertInstanceOf(AbstractTask::class, $task);
    }

    public function testCanBuildCustomTaskByName()
    {
        $tasks = $this->builder->buildTasks([Check::class]);

        $this->assertInstanceOf(Check::class, $tasks[0]);
    }

    public function testCanBuildTaskFromString()
    {
        $string = 'echo "I love ducks"';

        $string = $this->builder->buildTask($string);
        $this->assertInstanceOf(Closure::class, $string);

        $closure = $string->getClosure();
        $this->assertInstanceOf('Closure', $closure);

        $closureReflection = new ReflectionFunction($closure);
        $this->assertEquals(['stringTask' => 'echo "I love ducks"'], $closureReflection->getStaticVariables());

        $this->assertEquals('I love ducks', $string->execute());
    }

    public function testCanBuildTaskFromClosure()
    {
        $originalClosure = function ($task) {
            return $task->explainer->info('echo "I love ducks"');
        };

        $closure = $this->builder->buildTaskFromClosure($originalClosure);
        $this->assertInstanceOf(Closure::class, $closure);
        $this->assertEquals($originalClosure, $closure->getClosure());
    }

    public function testCanBuildTasks()
    {
        $queue = [
            'foobar',
            function () {
                return 'lol';
            },
            Deploy::class,
        ];

        $queue = $this->builder->buildTasks($queue);

        $this->assertInstanceOf(Closure::class, $queue[0]);
        $this->assertInstanceOf(Closure::class, $queue[1]);
        $this->assertInstanceOf(Deploy::class, $queue[2]);
    }

    public function testThrowsExceptionOnUnbuildableTask()
    {
        $this->setExpectedException(TaskCompositionException::class);

        $this->builder->buildTaskFromClass('Nope');
    }

    public function testCanBuildByCallable()
    {
        $task = $this->builder->buildTask([CallableTask::class, 'someMethod']);
        $this->assertEquals(Closure::class, $task->fire());

        $task = $this->builder->buildTask(CallableTask::class.'::someMethod');
        $this->assertEquals(Closure::class, $task->fire());
    }

    public function testCanUseInstancesFromTheContainerAsClasses()
    {
        $this->container->add('foobar', new CallableTask());

        $task = $this->builder->buildTask(['foobar', 'someMethod']);
        $this->assertEquals(Closure::class, $task->fire());
    }

    public function testBindsTasksToContainer()
    {
        $task = $this->builder->buildTask(DummyCoordinatedTask::class);
        $task->setOptions(['foo' => 'bar']);

        $task = $this->builder->buildTask(DummyCoordinatedTask::class);
        $this->assertEquals(['foo' => 'bar'], $task->getOptions());
    }

    public function testDoesntOverwriteNameAndDescription()
    {
        $task = $this->builder->buildTask(DummyShelledTask::class, 1, null);
        $this->assertEquals('Shelly', $task->getName());
        $this->assertContains('shells', $task->getDescription());

        $task = $this->builder->buildTask(DummyShelledTask::class, 'foo', 'bar');
        $this->assertEquals('Foo', $task->getName());
        $this->assertContains('bar', $task->getDescription());
    }

    public function testDoesntTryToNewNonTaskClasses()
    {
        $this->builder->buildTask(FlushCommand::class);
    }
}
