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

namespace Rocketeer\Services\Connections;

use Rocketeer\TestCases\RocketeerTestCase;

class ConnectionHandleTest extends RocketeerTestCase
{
    public function testCanCreateHandleForCurrent()
    {
        $handle = $this->credentials->createConnectionKey('foo', 2, 'staging');

        $this->assertEquals('foo/staging', $handle);
    }

    public function testDoesNotDisplayServerNumberIfNotMultiServer()
    {
        $handle = $this->credentials->createConnectionKey('foo', 0, 'staging');

        $this->assertEquals('foo/staging', $handle);
    }

    public function testCanUseHostnameOfServerInHandleIfPresent()
    {
        $this->swapConnections([
            'production' => [
                'servers' => [
                    ['host' => 'server1.com'],
                    ['host' => 'server2.com'],
                ],
            ],
        ]);

        $handle = $this->credentials->createConnectionKey('production', 1);

        $this->assertEquals('production/server2.com', $handle->toHandle());
    }

    public function testCanGetLocalHandle()
    {
        $this->rocketeer->setLocal(true);

        $this->assertEquals('local', $this->connections->getCurrentConnectionKey()->toHandle());
    }

    public function testCanGetLongHandle()
    {
        $this->rocketeer->setLocal(true);

        $key = $this->connections->getCurrentConnectionKey();
        $key->username = 'anahkiasen';

        $this->assertEquals('anahkiasen@local', $key->toLongHandle());
    }
}
