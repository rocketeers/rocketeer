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

namespace Rocketeer\Services\Connections\Credentials;

use Prophecy\Argument;
use Rocketeer\Services\Connections\Shell\Bash;
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

        $prophecy = $this->bindProphecy(Bash::class);
        $prophecy->onLocal(Argument::any())->willReturn('   foobar   ');

        $this->assertEquals('foobar', $this->credentials->getCurrentRepository()->branch);
    }

    public function testCanDefaultToMasterIfNoBranchFound()
    {
        $this->config->set('scm.branch', null);

        $prophecy = $this->bindProphecy(Bash::class);
        $prophecy->onLocal(Argument::any())->willReturn(null);

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

    public function testUsesConfigBeforeTryingToGuessBranch()
    {
        $prophecy = $this->prophesize(Bash::class);

        $this->credentials->getCurrentRepository();
        $prophecy->onLocal()->shouldNotHaveBeenCalled();
    }
}
