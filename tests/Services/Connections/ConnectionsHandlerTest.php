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

use Mockery\MockInterface;
use Rocketeer\Exceptions\ConnectionException;
use Rocketeer\Services\Tasks\TasksHandler;
use Rocketeer\TestCases\RocketeerTestCase;

class ConnectionsHandlerTest extends RocketeerTestCase
{
    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// TESTS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    public function testCanGetAvailableConnections()
    {
        $connections = $this->connections->getAvailableConnections();
        $this->assertEquals(['production', 'staging'], array_keys($connections));

        $this->localStorage->set('connections.custom.username', 'foobar');
        $connections = $this->connections->getAvailableConnections();
        $this->assertEquals(['production', 'staging', 'custom'], array_keys($connections));
    }

    public function testCanGetCurrentConnection()
    {
        $this->swapConfig(['default' => 'production']);
        $this->assertConnectionEquals('production');

        $this->swapConfig(['default' => 'staging']);
        $this->assertConnectionEquals('staging');
    }

    public function testCanChangeConnection()
    {
        $this->assertConnectionEquals('production');

        $this->connections->setCurrentConnection('staging');
        $this->assertConnectionEquals('staging');

        $this->connections->setActiveConnections('staging,production');
        $this->assertEquals(['staging', 'production'], $this->connections->getActiveConnections());
    }

    public function testFillsConnectionCredentialsHoles()
    {
        $connections = $this->connections->getAvailableConnections();
        $this->assertArrayHasKey('production', $connections);

        $this->localStorage->set('connections', [
            'staging' => [
                'host' => 'foobar',
                'username' => 'user',
                'password' => '',
                'keyphrase' => '',
                'key' => '/Users/user/.ssh/id_rsa',
                'agent' => '',
            ],
        ]);
        $connections = $this->connections->getAvailableConnections();
        $this->assertArrayHasKey('production', $connections);
    }

    public function testDoesntResetConnectionIfSameAsCurrent()
    {
        $this->mock(TasksHandler::class, TasksHandler::class, function (MockInterface $mock) {
            return $mock
                ->shouldReceive('registerConfiguredEvents')->once();
        }, false);

        $this->connections->setCurrentConnection('staging');
        $this->connections->setCurrentConnection('staging');
        $this->connections->setCurrentConnection('staging');
    }

    public function testDoesntResetStageIfSameAsCurrent()
    {
        $this->mock(TasksHandler::class, TasksHandler::class, function (MockInterface $mock) {
            return $mock
                ->shouldReceive('registerConfiguredEvents')->once();
        }, false);

        $this->connections->setStage('foobar');
        $this->connections->setStage('foobar');
        $this->connections->setStage('foobar');
    }

    public function testValidatesConnectionOnMultiset()
    {
        $this->connections->setActiveConnections(['production', 'bar']);

        $this->assertEquals(['production'], $this->connections->getActiveConnections());
    }

    public function testDoesntReuseConnectionIfDifferentServer()
    {
        $this->connections->setCurrentConnection('staging', 0);
        $this->assertConnectionEquals('staging');
        $this->assertCurrentServerEquals(0);

        $this->connections->setCurrentConnection('staging', 1);
        $this->assertConnectionEquals('staging');
        $this->assertCurrentServerEquals(1);
    }

    public function testThrowsExceptionWhenTryingToSetInvalidConnection()
    {
        $this->setExpectedException(ConnectionException::class, 'Invalid connection(s): foo, bar');

        $this->connections->setActiveConnections('foo,bar');
    }

    public function testFiresEventWhenConnectedToServer()
    {
        $this->expectOutputString('connected');

        $this->events->addListener('connected.production', function () {
            echo 'connected';
        });

        $this->swapConnections([
            'production' => [
                'host' => 'foobar.com',
                'username' => 'foobar',
                'password' => 'foobar',
            ],
        ]);

        $this->connections->getCurrentConnection();
    }
}
