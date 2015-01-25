<?php
namespace Rocketeer\Services\Connections;

use Rocketeer\TestCases\RocketeerTestCase;

class RemoteHandlerTest extends RocketeerTestCase
{
    /**
     * @type RemoteHandler
     */
    protected $handler;

    public function setUp()
    {
        parent::setUp();

        $this->handler = new RemoteHandler($this->app);
        unset($this->app['rocketeer.command']);
    }

    public function testCanCreateConnection()
    {
        $this->swapConfig(array(
            'rocketeer::connections' => array(
                'production' => array(
                    'host'     => 'foobar.com',
                    'username' => 'foobar',
                    'password' => 'foobar',
                ),
            ),
        ));

        $connection = $this->handler->connection();

        $this->assertInstanceOf('Rocketeer\Services\Connections\Connection', $connection);
        $this->assertEquals('production', $connection->getName());
        $this->assertEquals('foobar', $connection->getUsername());
    }

    public function testThrowsExceptionIfMissingCredentials()
    {
        $this->setExpectedException('Rocketeer\Exceptions\MissingCredentialsException');

        $this->swapConfig(array(
            'rocketeer::connections' => array(
                'production' => array(
                    'host'     => 'foobar.com',
                    'username' => 'foobar',
                ),
            ),
        ));

        $this->handler->connection();
    }

    public function testThrowsExceptionIfMissingInformations()
    {
        $this->setExpectedException('Rocketeer\Exceptions\MissingCredentialsException');

        $this->swapConfig(array(
            'rocketeer::connections' => array(
                'production' => array(
                    'username' => 'foobar',
                    'password' => 'foobar',
                ),
            ),
        ));

        $this->handler->connection();
    }

    public function testCachesConnections()
    {
        $this->swapConfig(array(
            'rocketeer::connections' => array(
                'production' => array(
                    'host'     => 'foobar.com',
                    'username' => 'foobar',
                    'password' => 'foobar',
                ),
            ),
        ));

        $connection = $this->handler->connection();
        $this->assertInstanceOf('Rocketeer\Services\Connections\Connection', $connection);
        $this->assertEquals('production', $connection->getName());

        $this->swapConfig(array(
            'rocketeer::connections' => array(
                'production' => array(),
            ),
        ));

        $connection = $this->handler->connection();
        $this->assertInstanceOf('Rocketeer\Services\Connections\Connection', $connection);
        $this->assertEquals('production', $connection->getName());
    }

    public function testThrowsExceptionIfUnableToConnect()
    {
        $this->setExpectedException('Rocketeer\Exceptions\ConnectionException');

        $this->swapConfig(array(
            'rocketeer::connections' => array(
                'production' => array(
                    'host'     => '127.0.0.1',
                    'username' => 'foobar',
                    'password' => 'foobar',
                ),
            ),
        ));

        $this->handler->run('ls');
    }

    public function testDoesntReturnWrongCredentials()
    {
        $this->swapConfig(
            array(
                'rocketeer::connections' => array(
                    'production' => array(
                        'servers' => array(
                            array(
                                'host'     => 'foo.com',
                                'username' => 'foo',
                                'password' => 'foo',
                            ),
                            array(
                                'host'     => 'bar.com',
                                'username' => 'bar',
                                'password' => 'bar',
                            ),
                        ),
                    ),
                ),
            )
        );
        /*
         * Setting connection to server 1
         */
        $this->connections->setConnection('production', 1);
        $connection = $this->handler->connection('production', 1);

        $this->assertInstanceOf('Rocketeer\Services\Connections\Connection', $connection);
        $this->assertEquals('production', $connection->getName());
        $this->assertEquals('bar', $connection->getUsername());

        /*
         * Setting connection to server 0
         */
        $this->connections->setConnection('production', 0);
        $connection = $this->handler->connection('production', 0);

        $this->assertInstanceOf('Rocketeer\Services\Connections\Connection', $connection);
        $this->assertEquals('production', $connection->getName());
        $this->assertEquals('foo', $connection->getUsername());
    }

    public function testSetsRolesOnCreation()
    {
        $this->swapConfig(array(
            'rocketeer::connections' => array(
                'production' => array(
                    'host'     => 'foobar.com',
                    'username' => 'foobar',
                    'password' => 'foobar',
                    'roles'    => ['foo', 'bar'],
                ),
            ),
        ));

        $connection = $this->handler->connection();

        $this->assertInstanceOf('Rocketeer\Services\Connections\Connection', $connection);
        $this->assertEquals(['foo', 'bar'], $connection->getRoles());
    }
}
