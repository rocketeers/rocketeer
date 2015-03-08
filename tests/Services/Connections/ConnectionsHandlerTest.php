<?php
namespace Rocketeer\Services\Connections;

use Mockery\MockInterface;
use Rocketeer\TestCases\RocketeerTestCase;

class ConnectionsHandlerTest extends RocketeerTestCase
{
    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// TESTS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    public function testCanGetAvailableConnections()
    {
        $connections = $this->connections->getAvailableConnections();
        $this->assertEquals(array('production', 'staging'), array_keys($connections));

        $this->app['rocketeer.storage.local']->set('connections.custom.username', 'foobar');
        $connections = $this->connections->getAvailableConnections();
        $this->assertEquals(array('production', 'staging', 'custom'), array_keys($connections));
    }

    public function testCanGetCurrentConnection()
    {
        $this->swapConfig(array('default' => 'production'));
        $this->assertConnectionEquals('production');

        $this->swapConfig(array('default' => 'staging'));
        $this->assertConnectionEquals('staging');
    }

    public function testCanChangeConnection()
    {
        $this->assertConnectionEquals('production');

        $this->connections->setConnection('staging');
        $this->assertConnectionEquals('staging');

        $this->connections->setConnections('staging,production');
        $this->assertEquals(array('staging', 'production'), $this->connections->getConnections());
    }

    public function testFillsConnectionCredentialsHoles()
    {
        $connections = $this->connections->getAvailableConnections();
        $this->assertArrayHasKey('production', $connections);

        $this->app['rocketeer.storage.local']->set('connections', array(
            'staging' => array(
                'host'      => 'foobar',
                'username'  => 'user',
                'password'  => '',
                'keyphrase' => '',
                'key'       => '/Users/user/.ssh/id_rsa',
                'agent'     => '',
            ),
        ));
        $connections = $this->connections->getAvailableConnections();
        $this->assertArrayHasKey('production', $connections);
    }

    public function testDoesntResetConnectionIfSameAsCurrent()
    {
        $this->mock('rocketeer.tasks', 'TasksHandler', function (MockInterface $mock) {
            return $mock
                ->shouldReceive('registerConfiguredEvents')->once();
        }, false);

        $this->connections->setConnection('staging');
        $this->connections->setConnection('staging');
        $this->connections->setConnection('staging');
    }

    public function testDoesntResetStageIfSameAsCurrent()
    {
        $this->mock('rocketeer.tasks', 'TasksHandler', function (MockInterface $mock) {
            return $mock
                ->shouldReceive('registerConfiguredEvents')->once();
        }, false);

        $this->connections->setStage('foobar');
        $this->connections->setStage('foobar');
        $this->connections->setStage('foobar');
    }

    public function testValidatesConnectionOnMultiset()
    {
        $this->connections->setConnections(['production', 'bar']);

        $this->assertEquals(['production'], $this->connections->getConnections());
    }

    public function testDoesntReuseConnectionIfDifferentServer()
    {
        $this->connections->setConnection('staging', 0);
        $this->assertConnectionEquals('staging');
        $this->assertCurrentServerEquals(0);

        $this->connections->setConnection('staging', 1);
        $this->assertConnectionEquals('staging');
        $this->assertCurrentServerEquals(1);
    }

    public function testThrowsExceptionWhenTryingToSetInvalidConnection()
    {
        $this->setExpectedException('Rocketeer\Exceptions\ConnectionException', 'Invalid connection(s): foo, bar');

        $this->connections->setConnections('foo,bar');
    }
}
