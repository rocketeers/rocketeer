<?php
namespace Rocketeer\Services\Connections;

use Rocketeer\TestCases\RocketeerTestCase;

class CoordinatorTest extends RocketeerTestCase
{
    public function testCanCoordinateTasks()
    {
        $this->expectOutputRegex('#'.
            'A:production/[ab]\.com/(master|develop)'.PHP_EOL.
            'A:production/[ab]\.com/(master|develop)'.PHP_EOL.
            'A:production/[ab]\.com/(master|develop)'.PHP_EOL.
            'A:production/[ab]\.com/(master|develop)'.PHP_EOL.
            'A:staging/[ab]\.com/(master|develop)'.PHP_EOL.
            'A:staging/[ab]\.com/(master|develop)'.PHP_EOL.
            'A:staging/[ab]\.com/(master|develop)'.PHP_EOL.
            'A:staging/[ab]\.com/(master|develop)'.PHP_EOL.
            'B:staging/[ab]\.com/(master|develop)'.PHP_EOL.
            'B:staging/[ab]\.com/(master|develop)'.PHP_EOL.
            'B:staging/[ab]\.com/(master|develop)'.PHP_EOL.
            'B:staging/[ab]\.com/(master|develop)'.PHP_EOL.
            'B:production/[ab]\.com/(master|develop)'.PHP_EOL.
            'B:production/[ab]\.com/(master|develop)'.PHP_EOL.
            'B:production/[ab]\.com/(master|develop)'.PHP_EOL.
            'B:production/[ab]\.com/(master|develop)'.PHP_EOL.
            'C:production/[ab]\.com/(master|develop)'.PHP_EOL.
            'C:production/[ab]\.com/(master|develop)'.PHP_EOL.
            'C:production/[ab]\.com/(master|develop)'.PHP_EOL.
            'C:production/[ab]\.com/(master|develop)'.PHP_EOL.
            'C:staging/[ab]\.com/(master|develop)'.PHP_EOL.
            'C:staging/[ab]\.com/(master|develop)'.PHP_EOL.
            'C:staging/[ab]\.com/(master|develop)'.PHP_EOL.
            'C:staging/[ab]\.com/(master|develop)'.PHP_EOL.'#'
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
