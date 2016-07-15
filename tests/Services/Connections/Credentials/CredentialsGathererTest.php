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

use Mockery;
use Mockery\MockInterface;
use Prophecy\Argument;
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
        $this->swapConfig([
            'connections' => [],
        ]);

        $this->mockAnswers([
            'No [host] is set for [production]' => $this->host,
            'No [username] is set for [production]' => $this->username,
            'No [password] is set for [production]' => $this->password,
        ]);

        $this->command->shouldReceive('askWith')->with('No connections have been set, please create one', 'production')->andReturn('production');
        $this->command->shouldReceive('askWith')->with(
            'No password or SSH key is set for [production], which would you use?',
            'key', ['key', 'password']
        )->andReturn('password');
        $this->command->shouldReceive('option')->andReturn(null);

        $this->credentialsGatherer->getConnectionsCredentials();

        $credentials = $this->credentials->getServerCredentials('production', 0);
        $this->assertEquals([
            'host' => $this->host,
            'username' => $this->username,
            'password' => $this->password,
            'keyphrase' => null,
            'key' => null,
            'agent' => null,
        ], $credentials);
    }

    public function testCanPassCredentialsAsFlags()
    {
        $this->swapConfig([
            'connections' => [],
        ]);

        $this->mockAnswers([
            'No [username] is set for [production]' => $this->username,
        ]);

        $this->command->shouldReceive('askWith')->with('No connections have been set, please create one', 'production')->andReturn('production');
        $this->command->shouldReceive('askWith')->with(
            'No password or SSH key is set for [production], which would you use?',
            'key', ['key', 'password']
        )->andReturn('password');
        $this->command->shouldReceive('option')->withNoArgs()->andReturn([
            'host' => $this->host,
            'password' => $this->password,
        ]);

        $this->credentialsGatherer->getConnectionsCredentials();

        $credentials = $this->credentials->getServerCredentials('production', 0);
        $this->assertEquals([
            'host' => $this->host,
            'username' => $this->username,
            'password' => $this->password,
            'keyphrase' => null,
            'key' => null,
            'agent' => null,
        ], $credentials);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Mock a set of question/answers.
     *
     * @param array $answers
     */
    protected function mockAnswers(array $answers = [])
    {
        $prophecy = $this->prophesize(AbstractCommand::class)
            ->willImplement(StyleInterface::class)
            ->willImplement(OutputInterface::class);

        if (!$answers) {
            $prophecy->ask(Argument::any())->shouldNotBeCaled();
        }

        foreach ($answers as $question => $answer) {
            $prophecy->ask(Argument::containingString($question))->shouldBeCalledTimes(1)->willReturn($answer);
        }

        $this->container->add('rocketeer.command', $prophecy->reveal());
    }
}
