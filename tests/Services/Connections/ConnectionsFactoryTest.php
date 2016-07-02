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

use Rocketeer\Services\Connections\Connections\Connection;
use Rocketeer\TestCases\RocketeerTestCase;

class ConnectionsFactoryTest extends RocketeerTestCase
{
    /**
     * @var ConnectionsFactory
     */
    protected $factory;

    public function setUp()
    {
        parent::setUp();

        $this->factory = new ConnectionsFactory($this->app);
    }

    public function testCanCreateConnection()
    {
        $this->swapConnections([
            'production' => [
                'host' => 'foobar.com',
                'username' => 'foobar',
                'password' => 'foobar',
            ],
        ]);

        $key = $this->connections->getCurrentConnectionKey();
        $connection = $this->factory->make($key);

        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertEquals('production', $connection->getName());
        $this->assertEquals('foobar', $connection->getUsername());
    }

    public function testCachesConnections()
    {
        $this->swapConnections([
            'production' => [
                'host' => 'foobar.com',
                'username' => 'foobar',
                'password' => 'foobar',
            ],
        ]);

        $key = $this->connections->getCurrentConnectionKey();
        $connection = $this->factory->make($key);
        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertEquals('production', $connection->getName());

        $this->swapConnections([
            'production' => [],
        ]);

        $key = $this->connections->getCurrentConnectionKey();
        $connection = $this->factory->make($key);
        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertEquals('production', $connection->getName());
    }

    public function testDoesntReturnWrongCredentials()
    {
        $this->swapConnections([
            'production' => [
                'servers' => [
                    [
                        'host' => 'foo.com',
                        'username' => 'foo',
                        'password' => 'foo',
                    ],
                    [
                        'host' => 'bar.com',
                        'username' => 'bar',
                        'password' => 'bar',
                    ],
                ],
            ],
        ]);

        // Setting connection to server 1
        $key = $this->credentials->createConnectionKey('production', 1);
        $connection = $this->factory->make($key);

        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertEquals('production', $connection->getName());
        $this->assertEquals('bar', $connection->getUsername());

        // Setting connection to server 0
        $key = $this->credentials->createConnectionKey('production', 0);
        $connection = $this->factory->make($key);

        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertEquals('production', $connection->getName());
        $this->assertEquals('foo', $connection->getUsername());
    }

    public function testSetsRolesOnCreation()
    {
        $this->swapConnections([
            'production' => [
                'host' => 'foobar.com',
                'username' => 'foobar',
                'password' => 'foobar',
                'roles' => ['foo', 'bar'],
            ],
        ]);

        $key = $this->connections->getCurrentConnectionKey();
        $connection = $this->factory->make($key);

        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertEquals(['foo', 'bar'], $connection->getRoles());
    }

    public function testCanPurgeCachedConnections()
    {
        $this->swapConnections([
            'production' => [
                'host' => 'foobar.com',
                'username' => 'foobar',
                'password' => 'foobar',
            ],
        ]);

        $key = $this->connections->getCurrentConnectionKey();
        $connection = $this->factory->make($key);
        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertEquals('production', $connection->getName());
        $this->assertEquals('foobar', $connection->getUsername());

        $this->swapConnections([
            'production' => [
                'host' => 'barbaz.com',
                'username' => 'barbaz',
                'password' => 'barbaz',
            ],
        ]);

        $this->factory->disconnect();
        $key = $this->connections->getCurrentConnectionKey();
        $connection = $this->factory->make($key);
        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertEquals('production', $connection->getName());
        $this->assertEquals('barbaz', $connection->getUsername());
    }
}
