<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Connections;

use Rocketeer\Dummies\Tasks\DummyCoordinatedTask;
use Rocketeer\TestCases\RocketeerTestCase;

class CoordinatorTest extends RocketeerTestCase
{
    public function testCanCoordinateTasks()
    {
        $pattern = '(staging|production)/[ab]\.com/(master|develop)'.PHP_EOL;

        $this->expectOutputRegex(
            '#'.
            'A:'.$pattern.
            'A:'.$pattern.
            'A:'.$pattern.
            'A:'.$pattern.
            'A:'.$pattern.
            'A:'.$pattern.
            'A:'.$pattern.
            'A:'.$pattern.
            'B:'.$pattern.
            'B:'.$pattern.
            'B:'.$pattern.
            'B:'.$pattern.
            'B:'.$pattern.
            'B:'.$pattern.
            'B:'.$pattern.
            'B:'.$pattern.
            'C:'.$pattern.
            'C:'.$pattern.
            'C:'.$pattern.
            'C:'.$pattern.
            'C:'.$pattern.
            'C:'.$pattern.
            'C:'.$pattern.
            'C:'.$pattern.
            '#'
        );

        $this->swapConfig([
            'stages.stages' => ['develop', 'master'],
            'stages.default' => ['develop', 'master'],
            'default' => ['production', 'staging'],
            'connections' => [
                'production' => [
                    'servers' => [
                        ['host' => 'a.com'],
                        ['host' => 'b.com'],
                    ],
                ],
                'staging' => [
                    'servers' => [
                        ['host' => 'a.com'],
                        ['host' => 'b.com'],
                    ],
                ],
            ],
        ]);

        $this->queue->execute(DummyCoordinatedTask::class, ['production', 'staging']);
    }
}
