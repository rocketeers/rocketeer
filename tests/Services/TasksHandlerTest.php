<?php
namespace Rocketeer\Services;

use Rocketeer\Dummies\DummyNotifier;
use Rocketeer\TestCases\RocketeerTestCase;

class TasksHandlerTest extends RocketeerTestCase
{
	public function testCanAddCommandsToArtisan()
	{
		$command = $this->tasks->add('Rocketeer\Tasks\Deploy');
		$this->assertInstanceOf('Rocketeer\Console\Commands\BaseTaskCommand', $command);
		$this->assertInstanceOf('Rocketeer\Tasks\Deploy', $command->getTask());
	}

	public function testCanGetTasksBeforeOrAfterAnotherTask()
	{
		$task   = $this->task('Deploy');
		$before = $this->tasks->getTasksListeners($task, 'before', true);

		$this->assertEquals(['before', 'foobar'], $before);
	}

	public function testCanAddTasksViaFacade()
	{
		$task   = $this->task('Deploy');
		$before = $this->tasks->getTasksListeners($task, 'before', true);

		$this->tasks->before('deploy', 'composer install');

		$newBefore = array_merge($before, array('composer install'));
		$this->assertEquals($newBefore, $this->tasks->getTasksListeners($task, 'before', true));
	}

	public function testCanAddMultipleTasksViaFacade()
	{
		$task  = $this->task('Deploy');
		$after = $this->tasks->getTasksListeners($task, 'after', true);
		$this->tasks->after('deploy', array(
			'composer install',
			'bower install',
		));

		$newAfter = array_merge($after, array('composer install', 'bower install'));
		$this->assertEquals($newAfter, $this->tasks->getTasksListeners($task, 'after', true));
	}

	public function testCanRegisterCustomTask()
	{
		$this->swapConfig(array(
			'rocketeer::default' => 'production',
		));

		$this->tasks->task('foobar', function ($task) {
			$task->runForCurrentRelease('ls');
		});

		$this->assertInstanceOf('Rocketeer\Tasks\Closure', $this->builder->buildTask('foobar'));

		$this->queue->run('foobar');
		$this->assertHistory([['cd {server}/releases/{release}', 'ls']]);
	}

	public function testCanRegisterCustomTaskViaArray()
	{
		$this->swapConfig(array(
			'rocketeer::default' => 'production',
		));

		$this->tasks->task('foobar', ['ls', 'ls']);
		$this->assertInstanceOf('Rocketeer\Tasks\Closure', $this->builder->buildTask('foobar'));

		$this->queue->run('foobar');
		$this->assertHistory([['cd {server}/releases/{release}', 'ls', 'ls']]);
	}

	public function testCanAddSurroundTasksToNonExistingTasks()
	{
		$task = $this->task('Setup');
		$this->tasks->after('setup', 'composer install');

		$after = array('composer install');
		$this->assertEquals($after, $this->tasks->getTasksListeners($task, 'after', true));
	}

	public function testCanAddSurroundTasksToMultipleTasks()
	{
		$this->tasks->after(array('cleanup', 'setup'), 'composer install');

		$after = array('composer install');
		$this->assertEquals($after, $this->tasks->getTasksListeners('setup', 'after', true));
		$this->assertEquals($after, $this->tasks->getTasksListeners('cleanup', 'after', true));
	}

	public function testCangetTasksListenersOrAfterAnotherTaskBySlug()
	{
		$after = $this->tasks->getTasksListeners('deploy', 'after', true);

		$this->assertEquals(array('after', 'foobar'), $after);
	}

	public function testCanAddEventsWithPriority()
	{
		$this->tasks->before('deploy', 'second', -5);
		$this->tasks->before('deploy', 'first');

		$listeners = $this->tasks->getTasksListeners('deploy', 'before', true);
		$this->assertEquals(['before', 'foobar', 'first', 'second'], $listeners);
	}

	public function testCanExecuteContextualEvents()
	{
		$this->swapConfig(array(
			'rocketeer::stages.stages'            => array('hasEvent', 'noEvent'),
			'rocketeer::on.stages.hasEvent.hooks' => array('before' => array('check' => 'ls')),
		));

		$this->connections->setStage('hasEvent');
		$this->assertEquals(['ls'], $this->tasks->getTasksListeners('check', 'before', true));

		$this->connections->setStage('noEvent');
		$this->assertEquals([], $this->tasks->getTasksListeners('check', 'before', true));
	}

	public function testCanbuildTasksFromConfigHook()
	{
		$tasks = array(
			'npm install',
			'bower install',
		);

		$this->swapConfig(array(
			'rocketeer::hooks' => ['after' => ['deploy' => $tasks]],
		));

		$this->tasks->registerConfiguredEvents();
		$listeners = $this->tasks->getTasksListeners('deploy', 'after', true);

		$this->assertEquals($tasks, $listeners);
	}

	public function testCanHaveCustomConnectionHooks()
	{
		$tasks = array(
			'npm install',
			'bower install',
		);

		$this->swapConfig(array(
			'rocketeer::default'                      => 'production',
			'rocketeer::hooks'                        => [],
			'rocketeer::on.connections.staging.hooks' => ['after' => ['deploy' => $tasks]],
		));
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
		$this->swapConfig(array(
			'rocketeer::hooks' => ['before' => ['deploy' => 'ls']],
		));

		$this->tasks->plugin(new DummyNotifier($this->app));

		$listeners = $this->tasks->getTasksListeners('deploy', 'before', true);
		$this->assertEquals(['ls', 'notify'], $listeners);

		$this->connections->setConnection('production');

		$listeners = $this->tasks->getTasksListeners('deploy', 'before', true);
		$this->assertEquals(['ls', 'notify'], $listeners);
	}

	public function testDoesntRegisterPluginsTwice()
	{
		$this->swapConfig(array(
			'rocketeer::hooks' => [],
		));

		$this->tasks->plugin(new DummyNotifier($this->app));
		$this->tasks->plugin(new DummyNotifier($this->app));
		$this->tasks->plugin(new DummyNotifier($this->app));

		$listeners = $this->tasks->getTasksListeners('deploy', 'before', true);
		$this->assertEquals(['notify'], $listeners);
	}
}
