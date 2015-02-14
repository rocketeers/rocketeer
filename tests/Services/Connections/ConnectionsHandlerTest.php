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
        $this->swapConfig(array('rocketeer::default' => 'foobar'));
        $this->assertConnectionEquals('production');

        $this->swapConfig(array('rocketeer::default' => 'production'));
        $this->assertConnectionEquals('production');

        $this->swapConfig(array('rocketeer::default' => 'staging'));
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

    public function testCanExtractCurrentBranchIfNoneSpecified()
    {
        $this->config->set('rocketeer::scm.branch', null);
        $this->mock('rocketeer.bash', 'Bash', function (MockInterface $mock) {
            return $mock->shouldReceive('onLocal')->andReturn('  foobar  ');
        });

        $this->assertEquals('foobar', $this->connections->getRepositoryBranch());
    }

    public function testCanDefaultToMasterIfNoBranchFound()
    {
        $this->config->set('rocketeer::scm.branch', null);
        $this->mock('rocketeer.bash', 'Bash', function (MockInterface $mock) {
            return $mock->shouldReceive('onLocal')->andReturn(null);
        });

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

        $this->assertArrayNotHasKey(2, $this->connections->getConnectionCredentials('production'));
    }

    public function testThrowsExceptionWhenTryingToSetInvalidConnection()
    {
        $this->setExpectedException('Rocketeer\Exceptions\ConnectionException', 'Invalid connection(s): foo, bar');

        $this->connections->setConnections('foo,bar');
    }

    public function testCanGetRepositoryName()
    {
        $this->assertEquals('Anahkiasen/html-object', $this->connections->getRepositoryName());
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// HELPERS ///////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Make the config return specific SCM config
     *
     * @param string $repository
     * @param string $username
     * @param string $password
     *
     * @return void
     */
    protected function expectRepositoryConfig($repository, $username, $password)
    {
        $this->swapConfig(array(
            'rocketeer::scm' => array(
                'repository' => $repository,
                'username'   => $username,
                'password'   => $password,
            ),
        ));
    }
}
