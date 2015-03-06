<?php
namespace Rocketeer\Services\Connections;

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

        $this->swapConfig(array(
            'stages.stages'  => ['develop', 'master'],
            'stages.default' => ['develop', 'master'],
            'default'        => ['production', 'staging'],
            'connections'    => array(
                'production' => [
                    'servers' => array(
                        ['host' => 'a.com'],
                        ['host' => 'b.com'],
                    ),
                ],
                'staging'    => [
                    'servers' => array(
                        ['host' => 'a.com'],
                        ['host' => 'b.com'],
                    ),
                ],
            ),
        ));

        $this->queue->execute('Rocketeer\Dummies\Tasks\DummyCoordinatedTask', ['production', 'staging']);
    }
}
