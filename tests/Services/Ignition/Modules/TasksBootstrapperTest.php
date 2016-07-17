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

namespace Rocketeer\Services\Ignition\Modules;

use Rocketeer\TestCases\RocketeerTestCase;

class TasksBootstrapperTest extends RocketeerTestCase
{
    public function testBindsAllTasksOntoContainer()
    {
        $this->assertTrue($this->container->has('rocketeer.tasks.create-release'));
    }
}
