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
use Prophecy\Prophecy\ObjectProphecy;
use Rocketeer\Console\Commands\AbstractCommand;
use Rocketeer\Console\StyleInterface;
use Rocketeer\TestCases\RocketeerTestCase;
use Symfony\Component\Console\Output\OutputInterface;

class CredentialsGathererTest extends RocketeerTestCase
{
    public function testCanGetRepositoryCredentials()
    {
        $this->swapRepositoryCredentials([]);
        $this->mockAnswers([
            'Where is your code' => 'https://'.$this->repository,
            'username' => $this->username,
            'password' => $this->password,
        ]);

        $credentials = $this->credentialsGatherer->getRepositoryCredentials();
        $this->assertEquals([
            'SCM_REPOSITORY' => 'https://'.$this->repository,
            'SCM_USERNAME' => $this->username,
            'SCM_PASSWORD' => $this->password,
        ], $credentials);
    }

    public function testDoesntAskForRepositoryCredentialsIfUneeded()
    {
        $this->swapRepositoryCredentials([]);
        $this->mockAnswers([
            'Where is' => $this->repository,
        ]);

        $credentials = $this->credentialsGatherer->getRepositoryCredentials();
        $this->assertEquals([
            'SCM_REPOSITORY' => $this->repository,
        ], $credentials);
    }

    public function testCanGetServerCredentialsIfNoneDefined()
    {
        $this->swapConnections([]);
        $this->mockAnswers([
            'create one' => 'foobar',
            'SSH key' => false,
            'located' => $this->host,
            'username' => $this->username,
            'password' => $this->password,
            'deployed' => '/foo/bar',
            'add a connection' => false,
        ]);

        $credentials = $this->credentialsGatherer->getConnectionsCredentials();
        $this->assertEquals([
            'foobar' => [
                'FOOBAR_HOST' => 'some.host',
                'FOOBAR_USERNAME' => 'anahkiasen',
                'FOOBAR_PASSWORD' => 'foobar',
                'FOOBAR_ROOT' => '/foo/bar',
            ],
        ], $credentials);
    }

    public function testCanPassCredentialsAsFlags()
    {
        $this->swapConnections([]);
        $prophecy = $this->mockAnswers([
            'create one' => 'foobar',
            'SSH key' => true,
            'deployed' => '/foo/bar',
            'add a connection' => false,
        ]);

        $prophecy->option('host')->willReturn($this->host);
        $prophecy->option('username')->willReturn($this->username);
        $prophecy->option('key')->willReturn('/.ssh/key');
        $prophecy->option('keyphrase')->willReturn('foobar');

        $credentials = $this->credentialsGatherer->getConnectionsCredentials();
        $this->assertEquals([
            'foobar' => [
                'FOOBAR_HOST' => 'some.host',
                'FOOBAR_USERNAME' => 'anahkiasen',
                'FOOBAR_ROOT' => '/foo/bar',
                'FOOBAR_KEY' => '/.ssh/key',
                'FOOBAR_KEYPHRASE' => 'foobar',
            ],
        ], $credentials);
    }
}
