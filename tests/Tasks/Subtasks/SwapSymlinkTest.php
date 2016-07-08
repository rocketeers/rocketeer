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

namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\TestCases\RocketeerTestCase;

class SwapSymlinkTest extends RocketeerTestCase
{
    public function testCanSwapCurrentSymlink()
    {
        $matcher = [
            [
                'ln -s {server}/releases/{release} {server}/current-temp',
                'mv -Tf {server}/current-temp {server}/current',
            ],
        ];

        $results = $this->assertTaskHistory('SwapSymlink', $matcher);
        $this->assertTrue($results);
    }
}
