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

namespace Rocketeer\Services\Connections\Shell;

use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;
use Rocketeer\TestCases\RocketeerTestCase;

class BashTest extends RocketeerTestCase
{
    public function testBashIsCorrectlyComposed()
    {
        $contents = $this->bash->runRaw('ls', true, true);

        $this->assertListDirectory($contents);
    }

    public function testCanSwitchBackToCorrectServer()
    {
        $this->swapConnections([
            'production' => [
                'servers' => [
                    ['host' => 'foo.com'],
                    ['host' => 'bar.com'],
                ],
            ],
        ]);

        $this->connections->setCurrentConnection('production', 1);
        $this->bash->on('local', function() {

        });

        $this->assertEquals(1, $this->connections->getCurrentConnectionKey()->server);
    }
}
