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

namespace Rocketeer\Services\Connections\Credentials\Modules;

use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;
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

        $this->connections->setCurrentConnection('production', 0);
        $this->assertEquals(['host' => 'server1.com'], $this->credentials->getServerCredentials());

        $this->connections->setCurrentConnection('production', 1);
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
            '--on' => 'production',
            '--server' => '0,1',
        ]);

        $this->assertArrayNotHasKey(2, $this->credentials->getServersCredentials('production'));
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
                'host' => 'foo.com',
                'roles' => ['foo', 'bar'],
            ],
        ]);

        $connection = $this->connections->getCurrentConnectionKey();
        $credentials = $this->credentials->syncConnectionCredentials($connection, ['host' => 'lol.com']);

        $this->assertEquals('lol.com', $credentials['host']);
        $this->assertEquals(['foo', 'bar'], $credentials['roles']);
        $this->assertEquals('lol.com', $this->connections->getCurrentConnectionKey()->host);
    }

    public function testCanHaveMultipleServerConnections()
    {
        $this->swapConnections([
            'production-multiserver' => [
                'servers' => $this->mockRuntimeMultiserverConnection(),
            ],
        ]);

        $this->mockCommand([
            '--on' => 'production-multiserver',
        ]);

        $this->credentials->getServerCredentials();

        $credentials = $this->credentials->getServerCredentials('production-multiserver', 0);
        $this->assertEquals([
            'host' => '10.1.1.1',
            'username' => $this->username,
            'agent' => true,
            'agent-forward' => true,
            'db_role' => false,
        ], $credentials);

        // also check handle generation as handles are used for connection cache keying in RemoteHandler
        $this->assertEquals('production-multiserver/10.1.1.1', $this->credentials->createConnectionKey('production-multiserver', 0));

        $credentials = $this->credentials->getServerCredentials('production-multiserver', 1);
        $this->assertEquals([
            'host' => '10.1.1.2',
            'username' => $this->username,
            'agent' => true,
            'agent-forward' => true,
            'db_role' => false,
        ], $credentials);

        $this->assertEquals('production-multiserver/10.1.1.2', $this->credentials->createConnectionKey('production-multiserver', 1));

        $credentials = $this->credentials->getServerCredentials('production-multiserver', 2);
        $this->assertEquals([
            'host' => '10.1.1.3',
            'username' => $this->username,
            'agent' => true,
            'agent-forward' => true,
            'db_role' => false,
        ], $credentials);

        $this->assertEquals('production-multiserver/10.1.1.3', $this->credentials->createConnectionKey('production-multiserver', 2));
    }

    //////////////////////////////////////////////////////////////////////
    //////////////////////////////// HELPERS /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Mock a set of runtime injected credentials.
     */
    protected function mockRuntimeMultiserverConnection()
    {
        return array_map(
            function ($ip) {
                return [
                    'host' => $ip,
                    'username' => $this->username,
                    'agent' => true,
                    'agent-forward' => true,
                    'db_role' => false,
                ];
            },
            ['10.1.1.1', '10.1.1.2', '10.1.1.3']
        );
    }
}
