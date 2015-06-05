<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services;

use Rocketeer\Dummies\DummyNotifier;
use Rocketeer\TestCases\RocketeerTestCase;

class TasksHandlerTest extends RocketeerTestCase
{
    public function testCanCreateCommandsWithTask()
    {
        $command = $this->tasks->add('Rocketeer\Tasks\Deploy');
        $this->assertInstanceOf('Rocketeer\Console\Commands\BaseTaskCommand', $command);
        $this->assertInstanceOf('Rocketeer\Tasks\Deploy', $command->getTask());
    }

    public function testCanGetTasksBeforeOrAfterAnotherTask()
    {
        $task = $this->task('Deploy');
        $before = $this->tasks->getTasksListeners($task, 'before', true);

        $this->assertEquals(['before', 'foobar'], $before);
    }

    public function testCanAddTasksViaFacade()
    {
        $this->disableTestEvents();

        $task = $this->task('Deploy');

        $this->tasks->before('deploy', 'composer install');

        $this->assertEquals(['composer install'], $this->tasks->getTasksListeners($task, 'before', true));
    }

    public function testCanAddMultipleTasksViaFacade()
    {
        $this->disableTestEvents();
        $task = $this->task('Deploy');
        $after = $this->tasks->getTasksListeners($task, 'after', true);
        $this->tasks->after('deploy', [
            'composer install',
            'bower install',
        ]);

        $this->assertEquals(['composer install', 'bower install'], $this->tasks->getTasksListeners($task, 'after', true));
    }

    public function testCanRegisterCustomTask()
    {
        $this->swapConfig([
            'default' => 'production',
        ]);

        $this->tasks->task('foobar', function ($task) {
            $task->runForCurrentRelease('ls');
        });

        $this->assertInstanceOf('Rocketeer\Tasks\Closure', $this->task('foobar'));

        $this->queue->run('foobar');
        $this->assertHistory([['cd {server}/releases/{release}', 'ls']]);
    }

    public function testCanRegisterCustomTaskViaArray()
    {
        $this->swapConfig([
            'default' => 'production',
        ]);

        $this->tasks->task('foobar', ['ls', 'ls']);
        $this->assertInstanceOf('Rocketeer\Tasks\Closure', $this->task('foobar'));

        $this->queue->run('foobar');
        $this->assertHistory([['cd {server}/releases/{release}', 'ls', 'ls']]);
    }

    public function testCanAddSurroundTasksToNonExistingTasks()
    {
        $task = $this->task('Setup');
        $this->tasks->after('setup', 'composer install');

        $after = ['composer install'];
        $this->assertEquals($after, $this->tasks->getTasksListeners($task, 'after', true));
    }

    public function testCanAddSurroundTasksToMultipleTasks()
    {
        $this->tasks->after(['cleanup', 'setup'], 'composer install');

        $after = ['composer install'];
        $this->assertEquals($after, $this->tasks->getTasksListeners('setup', 'after', true));
        $this->assertEquals($after, $this->tasks->getTasksListeners('cleanup', 'after', true));
    }

    public function testCangetTasksListenersOrAfterAnotherTaskBySlug()
    {
        $after = $this->tasks->getTasksListeners('deploy', 'after', true);

        $this->assertEquals(['after', 'foobar'], $after);
    }

    public function testCanAddEventsWithPriority()
    {
        $this->disableTestEvents();

        $this->tasks->before('deploy', 'second', -5);
        $this->tasks->before('deploy', 'first');

        $listeners = $this->tasks->getTasksListeners('deploy', 'before', true);
        $this->assertEquals(['first', 'second'], $listeners);
    }

    public function testCanExecuteContextualEvents()
    {
        $this->swapConfig([
            'stages.stages' => ['hasEvent', 'noEvent'],
            'on.stages.hasEvent.hooks' => ['before' => ['check' => 'ls']],
        ]);

        $this->connections->setStage('hasEvent');
        $this->assertEquals(['ls'], $this->tasks->getTasksListeners('check', 'before', true));

        $this->connections->setStage('noEvent');
        $this->assertEquals([], $this->tasks->getTasksListeners('check', 'before', true));
    }

    public function testCanbuildTasksFromConfigHook()
    {
        $tasks = [
            'npm install',
            'bower install',
        ];

        $this->swapConfig([
            'hooks' => ['after' => ['deploy' => $tasks]],
        ]);

        $this->tasks->registerConfiguredEvents();
        $listeners = $this->tasks->getTasksListeners('deploy', 'after', true);

        $this->assertEquals($tasks, $listeners);
    }

    public function testCanHaveCustomConnectionHooks()
    {
        $tasks = [
            'npm install',
            'bower install',
        ];

        $this->swapConfig([
            'default' => 'production',
            'hooks' => [],
            'on.connections.staging.hooks' => ['after' => ['deploy' => $tasks]],
        ]);
        $this->tasks->registerConfiguredEvents();

        $this->connections->setConnection('production');
        $events = $this->tasks->getTasksListeners('deploy', 'after', true);
        $this->assertEmpty($events);

        $this->connections->setConnection('staging');
        $events = $this->tasks->getTasksListeners('deploy', 'after', true);

        $this->assertEquals($tasks, $events);
    }

    public function testPluginsArentDeregisteredWhenSwitchingConnection()
    {
        $this->swapConfig([
            'hooks' => ['before' => ['deploy' => 'ls']],
        ]);

        $this->tasks->plugin(new DummyNotifier($this->app));

        $listeners = $this->tasks->getTasksListeners('deploy', 'before', true);
        $this->assertEquals(['ls', 'notify'], $listeners);

        $this->connections->setConnection('production');

        $listeners = $this->tasks->getTasksListeners('deploy', 'before', true);
        $this->assertEquals(['ls', 'notify'], $listeners);
    }

    public function testDoesntRegisterPluginsTwice()
    {
        $this->disableTestEvents();

        $this->tasks->plugin(new DummyNotifier($this->app));
        $this->tasks->plugin(new DummyNotifier($this->app));
        $this->tasks->plugin(new DummyNotifier($this->app));

        $listeners = $this->tasks->getTasksListeners('deploy', 'before', true);
        $this->assertEquals(['notify'], $listeners);
    }

    public function testCanBuildTasksFluently()
    {
        $this->tasks->task('phpunit')
                    ->does('foobar')
                    ->description('description');

        $task = $this->task('phpunit');

        $this->assertInstanceOf('Rocketeer\Tasks\Closure', $task);
        $this->assertEquals('description', $task->getDescription());
        $this->assertEquals('foobar', $task->getStringTask());
    }

    public function testCanDelegateCallsToTasks()
    {
        $this->tasks->configure('check', ['foo' => 'bar']);
        $task = $this->builder->buildTask('Check');

        $this->assertInstanceOf('Rocketeer\Tasks\Check', $task);
        $this->assertEquals(['foo' => 'bar'], $task->getOptions());
    }

    public function testCanDelegateCallsToStrategies()
    {
        $this->tasks->configureStrategy('Check', ['foo' => 'bar']);
        $this->tasks->configureStrategy(['Check', 'Ruby'], ['baz' => 'qux']);

        $php = $this->builder->buildStrategy('Check', 'Php');
        $ruby = $this->builder->buildStrategy('Check', 'Ruby');

        $this->assertInstanceOf('Rocketeer\Strategies\Check\PhpStrategy', $php);
        $this->assertInstanceOf('Rocketeer\Strategies\Check\RubyStrategy', $ruby);

        $this->assertEquals(['foo' => 'bar'], $php->getOptions());
        $this->assertEquals(['baz' => 'qux'], $ruby->getOptions());
    }

    public function testCanAddLookupsViaPlugins()
    {
        $this->tasks->plugin('Rocketeer\Dummies\Plugins\DummyBuilderPlugin');

        $task = $this->builder->buildTask('MyCustomTask');

        $this->assertInstanceOf('Rocketeer\Dummies\Tasks\MyCustomTask', $task);
    }

    public function testCanUseCallableAsEventListener()
    {
        $this->expectOutputString('FIRED');
        $this->disableTestEvents();

        $this->tasks->listenTo('deploy.before', ['Rocketeer\Dummies\Tasks\CallableTask', 'fire']);
        $this->task('Deploy')->fireEvent('before');
    }
}
