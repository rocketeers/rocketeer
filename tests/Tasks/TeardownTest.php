<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Tasks;

use Mockery\MockInterface;
use Rocketeer\TestCases\RocketeerTestCase;

class TeardownTest extends RocketeerTestCase
{
    public function testCanTeardownServer()
    {
        $this->mock('rocketeer.storage.local', 'LocalStorage', function (MockInterface $mock) {
            return $mock
                ->shouldReceive('getSeparator')->andReturn(DIRECTORY_SEPARATOR)
                ->shouldReceive('destroy')->once();
        });

        $this->assertTaskHistory('Teardown', [
            'rm -rf {server}/',
        ]);
    }

    public function testCanAbortTeardown()
    {
        $this->mock('rocketeer.storage.local', 'LocalStorage', function (MockInterface $mock) {
            return $mock
                ->shouldReceive('getSeparator')->andReturn(DIRECTORY_SEPARATOR)
                ->shouldReceive('destroy')->never();
        });

        $task    = $this->pretendTask('Teardown', [], ['confirm' => false]);
        $message = $this->assertTaskHistory($task, []);

        $this->assertContains('Teardown aborted', $message);
    }
}
