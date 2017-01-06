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

namespace Rocketeer\Services\Connections;

use Rocketeer\TestCases\RocketeerTestCase;

class LocalConnectionTest extends RocketeerTestCase
{
    public function testCanGetPreviousStatus()
    {
        $task = $this->task;
        $task->setLocal(true);
        $task->run('ls');

        $this->assertTrue($task->status());
    }
}
