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

use Rocketeer\Services\Credentials\Keys\ConnectionKey;
use Rocketeer\TestCases\RocketeerTestCase;

class ConnectionsKeychainTest extends RocketeerTestCase
{
    public function testUsesCurrentServerWhenGettingServerCredentials()
    {
        $this->swapConnections([
            'production' => [
                'servers' => [
                    ['host' => 'server1.com'],
                    ['host' => 'server2.com'],
                ],
            ],
        ]);

        $this->connections->setConnection('production', 0);
        $this->assertEquals(['host' => 'server1.com'], $this->credentials->getServerCredentials());

        $this->connections->setConnection('production', 1);
        $this->assertEquals(['host' => 'server2.com'], $this->credentials->getServerCredentials());
    }

    public function testCanSpecifyServersViaOptions()
    {
        $this->swapConnections([
            'production' => [
                'servers' => [
                    ['host' => 'server1.com'],
                    ['host' => 'server2.com'],
                    ['host' => 'server3.com'],
                ],
            ],
        ]);

        $this->mockCommand([
            'on'     => 'production',
            'server' => '0,1',
        ]);

        $this->assertArrayNotHasKey(2, $this->credentials->getConnectionCredentials('production'));
    }

    public function testAlwaysReturnsArrayIfNoCredentialsFound()
    {
        $key = new ConnectionKey();

        $this->assertEquals([], $key->getServerCredentials());
    }

    public function testDoesntOverrideExtraCredentials()
    {
        $this->swapConfig([
            'connections.production.servers.0' => [
                'host'  => 'foo.com',
                'roles' => ['foo', 'bar'],
            ],
        ]);

        $connection  = $this->connections->getCurrentConnection();
        $credentials = $this->credentials->syncConnectionCredentials($connection, ['host' => 'lol.com']);

        $this->assertEquals('lol.com', $credentials['host']);
        $this->assertEquals(['foo', 'bar'], $credentials['roles']);
    }
}
