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

class ConnectionsHandlerTest extends RocketeerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->username = 'Anahkiasen';
        $this->password = 'foobar';
        $this->host = 'some.host';
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// TESTS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    public function testCanGetAvailableConnections()
    {
        $connections = $this->connections->getAvailableConnections();
        $this->assertEquals(['production', 'staging'], array_keys($connections));

        $this->app['rocketeer.storage.local']->set('connections.custom.username', 'foobar');
        $connections = $this->connections->getAvailableConnections();
        $this->assertEquals(['production', 'staging', 'custom'], array_keys($connections));
    }

    public function testCanGetCurrentConnection()
    {
        $this->swapConfig(['rocketeer::default' => 'foobar']);
        $this->assertConnectionEquals('production');

        $this->swapConfig(['rocketeer::default' => 'production']);
        $this->assertConnectionEquals('production');

        $this->swapConfig(['rocketeer::default' => 'staging']);
        $this->assertConnectionEquals('staging');
    }

    public function testCanChangeConnection()
    {
        $this->assertConnectionEquals('production');

        $this->connections->setConnection('staging');
        $this->assertConnectionEquals('staging');

        $this->connections->setConnections('staging,production');
        $this->assertEquals(['staging', 'production'], $this->connections->getConnections());
    }

    public function testUsesCurrentServerWhenGettingServerCredentials()
    {
        $this->swapConfig([
            'rocketeer::connections' => [
                'production' => [
                    'servers' => [
                        ['host' => 'server1.com'],
                        ['host' => 'server2.com'],
                    ],
                ],
            ],
        ]);

        $this->connections->setConnection('production', 0);
        $this->assertEquals(['host' => 'server1.com'], $this->connections->getServerCredentials());

        $this->connections->setConnection('production', 1);
        $this->assertEquals(['host' => 'server2.com'], $this->connections->getServerCredentials());
    }

    public function testCanUseSshRepository()
    {
        $repository = 'git@github.com:'.$this->repository;
        $this->expectRepositoryConfig($repository, '', '');

        $this->assertRepositoryEquals($repository);
    }

    public function testCanUseHttpsRepository()
    {
        $this->expectRepositoryConfig('https://github.com/'.$this->repository, 'foobar', 'bar');

        $this->assertRepositoryEquals('https://foobar:bar@github.com/'.$this->repository);
    }

    public function testCanUseHttpsRepositoryWithUsernameProvided()
    {
        $this->expectRepositoryConfig('https://foobar@github.com/'.$this->repository, 'foobar', 'bar');

        $this->assertRepositoryEquals('https://foobar:bar@github.com/'.$this->repository);
    }

    public function testCanUseHttpsRepositoryWithOnlyUsernameProvided()
    {
        $this->expectRepositoryConfig('https://foobar@github.com/'.$this->repository, 'foobar', '');

        $this->assertRepositoryEquals('https://foobar@github.com/'.$this->repository);
    }

    public function testCanCleanupProvidedRepositoryFromCredentials()
    {
        $this->expectRepositoryConfig('https://foobar@github.com/'.$this->repository, 'Anahkiasen', '');

        $this->assertRepositoryEquals('https://Anahkiasen@github.com/'.$this->repository);
    }

    public function testCanUseHttpsRepositoryWithoutCredentials()
    {
        $this->expectRepositoryConfig('https://github.com/'.$this->repository, '', '');

        $this->assertRepositoryEquals('https://github.com/'.$this->repository);
    }

    public function testCanCheckIfRepositoryNeedsCredentials()
    {
        $this->expectRepositoryConfig('https://github.com/'.$this->repository, '', '');
        $this->assertTrue($this->connections->needsCredentials());
    }

    public function testCangetRepositoryBranch()
    {
        $this->assertEquals('master', $this->connections->getRepositoryBranch());
    }

    public function testCanPassRepositoryBranchAsFlag()
    {
        $this->mockCommand(['branch' => '1.0']);

        $this->assertEquals('1.0', $this->connections->getRepositoryBranch());
    }

    public function testFillsConnectionCredentialsHoles()
    {
        $connections = $this->connections->getAvailableConnections();
        $this->assertArrayHasKey('production', $connections);

        $this->app['rocketeer.storage.local']->set('connections', [
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

    public function testCanCreateHandleForCurrent()
    {
        $handle = $this->connections->getHandle('foo', 2, 'staging');

        $this->assertEquals('foo/2/staging', $handle);
    }

    public function testDoesntDisplayServerNumberIfNotMultiserver()
    {
        $handle = $this->connections->getHandle('foo', 0, 'staging');

        $this->assertEquals('foo/staging', $handle);
    }

    public function testDoesntResetConnectionIfSameAsCurrent()
    {
        $this->mock('rocketeer.tasks', 'TasksHandler', function ($mock) {
            return $mock
                ->shouldReceive('registerConfiguredEvents')->once();
        }, false);

        $this->connections->setConnection('staging');
        $this->connections->setConnection('staging');
        $this->connections->setConnection('staging');
    }

    public function testDoesntResetStageIfSameAsCurrent()
    {
        $this->mock('rocketeer.tasks', 'TasksHandler', function ($mock) {
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

    public function testAlwaysReturnsArrayIfNoCredentialsFound()
    {
        $this->assertEquals([], $this->connections->getServerCredentials());
    }

    public function testCanHaveMultipleServerConnections()
    {
        $this->swapConfig([
            'rocketeer::connections' => [
                'production-multiserver' => [
                    'servers' => $this->mockRuntimeMultiserverConnection(),
                ],
            ],
        ]);
        $this->mockCommand([
            'on' => 'production-multiserver',
        ]);
        $this->credentials->getServerCredentials();

        $credentials = $this->connections->getServerCredentials('production-multiserver', 0);
        $this->assertEquals([
            'host' => '10.1.1.1',
            'username' => $this->username,
            'password' => '',
            'keyphrase' => '',
            'key' => '',
            'agent' => true,
            'agent-forward' => true,
            'db_role' => false,
        ], $credentials);
        // also check handle generation as handles are used for connection cache keying in RemoteHandler
        $this->assertEquals('production-multiserver/0', $this->connections->getHandle('production-multiserver', 0));

        $credentials = $this->connections->getServerCredentials('production-multiserver', 1);
        $this->assertEquals([
            'host' => '10.1.1.2',
            'username' => $this->username,
            'password' => '',
            'keyphrase' => '',
            'key' => '',
            'agent' => true,
            'agent-forward' => true,
            'db_role' => false,
        ], $credentials);
        $this->assertEquals('production-multiserver/1', $this->connections->getHandle('production-multiserver', 1));

        $credentials = $this->connections->getServerCredentials('production-multiserver', 2);
        $this->assertEquals([
            'host' => '10.1.1.3',
            'username' => $this->username,
            'password' => '',
            'keyphrase' => '',
            'key' => '',
            'agent' => true,
            'agent-forward' => true,
            'db_role' => false,
        ], $credentials);
        $this->assertEquals('production-multiserver/2', $this->connections->getHandle('production-multiserver', 2));
    }

    public function testCanExpandPathsAtRuntime()
    {
        $this->swapConfig([
            'rocketeer::connections' => [
                'production' => [
                    'host' => 'foo.com',
                    'key' => '~/.ssh/id_rsa',
                ],
            ],
        ]);

        $this->assertEquals($this->paths->getUserHomeFolder().'/.ssh/id_rsa', $this->connections->getServerCredentials()['key']);
    }

    public function testCanUseRuntimeOptions()
    {
        $this->mockCommand([
            'key' => 'foobar',
        ]);

        $this->swapConfig([
            'rocketeer::connections' => [
                'production' => [
                    'host' => 'foo.com',
                    'key' => '~/.ssh/id_rsa',
                ],
            ],
        ]);

        $this->assertEquals('foobar', $this->connections->getServerCredentials()['key']);
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// HELPERS ///////////////////////////
    ////////////////////////////////////////////////////////////////////

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

    /**
     * Make the config return specific SCM config.
     *
     * @param string $repository
     * @param string $username
     * @param string $password
     */
    protected function expectRepositoryConfig($repository, $username, $password)
    {
        $this->swapConfig([
            'rocketeer::scm' => [
                'repository' => $repository,
                'username' => $username,
                'password' => $password,
            ],
        ]);
    }
}
