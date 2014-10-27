<?php
namespace Rocketeer\Services\Ignition;

use Rocketeer\TestCases\RocketeerTestCase;

class TasksTest extends RocketeerTestCase
{
	public function testCustomTasksAreProperlyBoundToContainer()
	{
		$userTasks = (array) $this->app['config']->get('rocketeer::hooks.custom');
		$this->app['rocketeer.igniter.tasks']->registerTasksAndCommands($userTasks);

		$this->assertInstanceOf('Rocketeer\Dummies\MyCustomTask', $this->app['rocketeer.tasks.my-custom-task']);
	}
}
