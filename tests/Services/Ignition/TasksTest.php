<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Ignition;

use Rocketeer\Dummies\Tasks\MyCustomTask;
use Rocketeer\Tasks\Closure;
use Rocketeer\TestCases\RocketeerTestCase;

class TasksTest extends RocketeerTestCase
{
    public function testCustomTasksAreProperlyBoundToContainer()
    {
        $userTasks = (array) $this->config->get('hooks.custom');
        $this->container->get('igniter.tasks')->registerTasksAndCommands($userTasks);

        $this->assertInstanceOf(MyCustomTask::class, $this->container->get('rocketeer.tasks.my-custom-task'));
    }

    public function testCanComputeSlugWithoutTask()
    {
        $slug = $this->container->get('igniter.tasks')->getTaskHandle('foobar');

        $this->assertEquals('foobar', $slug);
    }

    public function testCanComputeSlugWithClosureTask()
    {
        $task = new Closure($this->container);
        $slug = $this->container->get('igniter.tasks')->getTaskHandle('foobar', $task);

        $this->assertEquals('foobar', $slug);
    }
}
