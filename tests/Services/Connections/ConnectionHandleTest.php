<?php
namespace Rocketeer\Services\Connections;

use Rocketeer\TestCases\RocketeerTestCase;

class ConnectionHandleTest extends RocketeerTestCase
{
    public function testCanCreateHandleForCurrent()
    {
        $handle = $this->credentials->createHandle('foo', 2, 'staging');

        $this->assertEquals('foo/staging', $handle);
    }

    public function testDoesntDisplayServerNumberIfNotMultiserver()
    {
        $handle = $this->credentials->createHandle('foo', 0, 'staging');

        $this->assertEquals('foo/staging', $handle);
    }

    public function testCanUseHostnameOfServerInHandleIfPresent()
    {
        $this->swapConnections(array(
            'production' => array(
                'servers' => array(
                    ['host' => 'server1.com'],
                    ['host' => 'server2.com'],
                ),
            ),
        ));

        $handle = $this->credentials->createHandle('production', 1);

        $this->assertEquals('production/server2.com', $handle->toHandle());
    }

    public function testCanGetLocalHandle()
    {
        $this->rocketeer->setLocal(true);

        $this->assertEquals('local', $this->connections->getCurrentConnection());
    }

    public function testCanGetLongHandle()
    {
        $this->rocketeer->setLocal(true);

        $this->assertEquals('anahkiasen@local', $this->connections->getCurrentConnection()->toLongHandle());
    }
}
