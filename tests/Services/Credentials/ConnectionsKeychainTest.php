<?php
namespace Rocketeer\Services\Connections;

use Rocketeer\Services\Credentials\Keys\ConnectionKey;
use Rocketeer\TestCases\RocketeerTestCase;

class ConnectionsKeychainTest extends RocketeerTestCase
{
    public function testUsesCurrentServerWhenGettingServerCredentials()
    {
        $this->swapConnections(array(
            'production' => array(
                'servers' => array(
                    ['host' => 'server1.com'],
                    ['host' => 'server2.com'],
                ),
            ),
        ));

        $this->connections->setConnection('production', 0);
        $this->assertEquals(['host' => 'server1.com'], $this->credentials->getServerCredentials());

        $this->connections->setConnection('production', 1);
        $this->assertEquals(['host' => 'server2.com'], $this->credentials->getServerCredentials());
    }

    public function testCanSpecifyServersViaOptions()
    {
        $this->swapConnections(array(
            'production' => array(
                'servers' => array(
                    ['host' => 'server1.com'],
                    ['host' => 'server2.com'],
                    ['host' => 'server3.com'],
                ),
            ),
        ));

        $this->mockCommand(array(
            'on'     => 'production',
            'server' => '0,1',
        ));

        $this->assertArrayNotHasKey(2, $this->credentials->getConnectionCredentials('production'));
    }

    public function testAlwaysReturnsArrayIfNoCredentialsFound()
    {
        $key = new ConnectionKey();

        $this->assertEquals([], $key->getServerCredentials());
    }
}
