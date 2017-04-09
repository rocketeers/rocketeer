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

use Rocketeer\TestCases\RocketeerTestCase;

class CredentialsGathererTest extends RocketeerTestCase
{
    public function testCanGetRepositoryCredentials()
    {
        $this->swapVcsConfiguration([]);
        $this->mockAnswers([
            'Where is your code' => 'https://'.$this->repository,
            'username' => $this->username,
            'password' => $this->password,
        ]);

        $credentials = $this->credentialsGatherer->getRepositoryCredentials();
        $this->assertEquals([
            'VCS_REPOSITORY' => 'https://'.$this->repository,
            'VCS_USERNAME' => $this->username,
            'VCS_PASSWORD' => $this->password,
        ], $credentials);
    }

    public function testDoesNotAskForRepositoryCredentialsIfUnneeded()
    {
        $this->swapVcsConfiguration([]);
        $this->mockAnswers([
            'Where is' => $this->repository,
        ]);

        $credentials = $this->credentialsGatherer->getRepositoryCredentials();
        $this->assertEquals([
            'VCS_REPOSITORY' => $this->repository,
        ], $credentials);
    }

    public function testCanGetServerCredentialsIfNoneDefined()
    {
        $this->swapConnections([]);
        $this->mockAnswers([
            'create one' => 'foobar',
            'SSH key' => false,
            'located' => 'some.host',
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
                'FOOBAR_ROOT_DIRECTORY' => '/foo/bar',
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

        $prophecy->option('host')->willReturn('some.host');
        $prophecy->option('username')->willReturn($this->username);
        $prophecy->option('key')->willReturn('/.ssh/key');
        $prophecy->option('keyphrase')->willReturn('foobar');

        $credentials = $this->credentialsGatherer->getConnectionsCredentials();
        $this->assertEquals([
            'foobar' => [
                'FOOBAR_HOST' => 'some.host',
                'FOOBAR_USERNAME' => 'anahkiasen',
                'FOOBAR_ROOT_DIRECTORY' => '/foo/bar',
                'FOOBAR_KEY' => '/.ssh/key',
                'FOOBAR_KEYPHRASE' => 'foobar',
            ],
        ], $credentials);
    }
}
