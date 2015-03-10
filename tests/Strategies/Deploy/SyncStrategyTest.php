<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Strategies\Deploy;

use Rocketeer\TestCases\RocketeerTestCase;

class SyncStrategyTest extends RocketeerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->swapConfig([
            'rocketeer::connections' => [
                'production' => [
                    'host'     => 'bar.com',
                    'username' => 'foo',
                ],
            ],
        ]);
    }

    public function testCanDeployRepository()
    {
        $task = $this->pretendTask('Deploy');
        $task->getStrategy('Deploy', 'Sync')->deploy();

        $matcher = [
            'mkdir {server}/releases/{release}',
            'rsync ./ foo@bar.com:{server}/releases/{release} --verbose --recursive --rsh="ssh" --exclude=".git" --exclude="vendor"',
        ];

        $this->assertHistory($matcher);
    }

    public function testCanUpdateRepository()
    {
        $task = $this->pretendTask('Deploy');
        $task->getStrategy('Deploy', 'Sync')->update();

        $matcher = [
            'rsync ./ foo@bar.com:{server}/releases/{release} --verbose --recursive --rsh="ssh" --exclude=".git" --exclude="vendor"',
        ];

        $this->assertHistory($matcher);
    }
}
