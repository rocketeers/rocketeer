<?php
namespace Rocketeer\Services\Connections;

use Rocketeer\TestCases\RocketeerTestCase;

class CoordinatorTest extends RocketeerTestCase
{
    public function testCanCoordinateTasks()
    {
        $this->expectOutputString(
            'A:production/a.com/develop'.PHP_EOL.
            'A:production/a.com/master'.PHP_EOL.
            'A:production/b.com/develop'.PHP_EOL.
            'A:production/b.com/master'.PHP_EOL.
            'A:staging/a.com/develop'.PHP_EOL.
            'A:staging/a.com/master'.PHP_EOL.
            'A:staging/b.com/develop'.PHP_EOL.
            'A:staging/b.com/master'.PHP_EOL.
            'B:staging/a.com/master'.PHP_EOL.
            'B:staging/b.com/develop'.PHP_EOL.
            'B:staging/b.com/master'.PHP_EOL.
            'B:staging/a.com/develop'.PHP_EOL.
            'B:production/b.com/master'.PHP_EOL.
            'B:production/a.com/master'.PHP_EOL.
            'B:production/b.com/develop'.PHP_EOL.
            'B:production/a.com/develop'.PHP_EOL.
            'C:production/a.com/master'.PHP_EOL.
            'C:production/b.com/develop'.PHP_EOL.
            'C:production/a.com/develop'.PHP_EOL.
            'C:production/b.com/master'.PHP_EOL.
            'C:staging/a.com/develop'.PHP_EOL.
            'C:staging/b.com/develop'.PHP_EOL.
            'C:staging/b.com/master'.PHP_EOL.
            'C:staging/a.com/master'.PHP_EOL
        );

        $this->swapConfig(array(
            'stages.stages'  => ['develop', 'master'],
            'stages.default' => ['develop', 'master'],
            'default'        => ['production', 'staging'],
            'connections'    => array(
                'production' => [
                    'servers' => array(
                        ['host' => 'a.com'], ['host' => 'b.com'],
                    ),
                ],
                'staging'    => [
                    'servers' => array(
                        ['host' => 'a.com'], ['host' => 'b.com'],
                    ),
                ],
            ),
        ));

        $this->queue->execute('Rocketeer\Dummies\Tasks\DummyCoordinatedTask', ['production', 'staging']);
    }
}
