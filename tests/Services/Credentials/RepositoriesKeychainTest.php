<?php
namespace Rocketeer\Services\Credentials;

use Mockery\MockInterface;
use Rocketeer\TestCases\RocketeerTestCase;

class RepositoriesKeychainTest extends RocketeerTestCase
{
    public function testCanGetRepositoryName()
    {
        $this->assertEquals('Anahkiasen/html-object', $this->credentials->getCurrentRepository()->getName());
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
        $this->assertTrue($this->credentials->getCurrentRepository()->needsCredentials());
    }

    public function testCangetRepositoryBranch()
    {
        $this->assertEquals('master', $this->credentials->getCurrentRepository()->branch);
    }

    public function testCanExtractCurrentBranchIfNoneSpecified()
    {
        $this->config->set('scm.branch', null);
        $this->mock('rocketeer.bash', 'Bash', function (MockInterface $mock) {
            return $mock->shouldReceive('onLocal')->andReturn('  foobar  ');
        });

        $this->assertEquals('foobar', $this->credentials->getCurrentRepository()->branch);
    }

    public function testCanDefaultToMasterIfNoBranchFound()
    {
        $this->config->set('scm.branch', null);
        $this->mock('rocketeer.bash', 'Bash', function (MockInterface $mock) {
            return $mock->shouldReceive('onLocal')->andReturn(null);
        });

        $this->assertEquals('master', $this->credentials->getCurrentRepository()->branch);
    }

    public function testCanPassRepositoryBranchAsFlag()
    {
        $this->mockCommand(['branch' => '1.0']);

        $this->assertEquals('1.0', $this->credentials->getCurrentRepository()->branch);
    }

    public function testCanProperlyEncodeAuthenticationParameters()
    {
        $this->expectRepositoryConfig('https://github.com/foo/bar', 'foo@bar.com', 'fo$obar');

        $this->assertRepositoryEquals('https://foo%40bar.com:fo%24obar@github.com/foo/bar');
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// HELPERS ///////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Make the config return specific SCM config.
     *
     * @param string $repository
     * @param string $username
     * @param string $password
     */
    protected function expectRepositoryConfig($repository, $username, $password)
    {
        $this->swapConfig(array(
            'scm' => array(
                'repository' => $repository,
                'username'   => $username,
                'password'   => $password,
            ),
        ));
    }
}
