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

namespace Rocketeer\Services\Filesystem;

use League\Flysystem\Sftp\SftpAdapter;
use phpseclib\System\SSH\Agent;
use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;
use Rocketeer\TestCases\RocketeerTestCase;

class ConnectionKeyAdapterFactoryTest extends RocketeerTestCase
{
    public function testCanProperlyEnableAgentForwarding()
    {
        if (!isset($_SERVER['SSH_AUTH_SOCK'])) {
            $this->markTestSkipped('No agent forwarding on this platform');
        }

        $connectionKey = new ConnectionKey([
            'server' => 0,
            'servers' => [[
                'host' => 'foo.com',
                'username' => 'foo',
                'agent' => true,
            ]],
        ]);

        /** @var SftpAdapter $adapter */
        $adapter = (new ConnectionKeyAdapterFactory())->getAdapter($connectionKey);
        $this->assertInstanceOf(Agent::class, $adapter->getAuthentication());
    }

    public function testCanDisableAgentIfNotPossible()
    {
        unset($_SERVER['SSH_AUTH_SOCK']);
        $connectionKey = new ConnectionKey([
            'server' => 0,
            'servers' => [[
                'host' => 'foo.com',
                'username' => 'foo',
                'agent' => true,
            ]],
        ]);

        /** @var SftpAdapter $adapter */
        $adapter = (new ConnectionKeyAdapterFactory())->getAdapter($connectionKey);
        $this->assertNull($adapter->getAuthentication());
    }
}
