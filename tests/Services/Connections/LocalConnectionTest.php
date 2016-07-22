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

use Rocketeer\Tasks\AbstractTask;
use Rocketeer\TestCases\RocketeerTestCase;

class LocalConnectionTest extends RocketeerTestCase
{
    public function testCanGetPreviousStatus()
    {
        $this->task->runLocally('ls');

        $this->assertTrue($this->task->status());
    }

    public function testCanExecuteCommandInDirectory()
    {
        $results = $this->task->on('local', function (AbstractTask $task) {
            return $task->runForCurrentRelease('pwd');
        });

        $this->assertEquals(realpath($this->server.'/releases/20000000000000'), $results);
    }
}
