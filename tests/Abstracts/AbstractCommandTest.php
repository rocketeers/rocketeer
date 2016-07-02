<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Abstracts;

use Mockery;
use Mockery\MockInterface;
use Rocketeer\Console\Commands\Plugins\InstallCommand;
use Rocketeer\Dummies\DummyFailingCommand;
use Rocketeer\Services\History\LogsHandler;
use Rocketeer\TestCases\RocketeerTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class AbstractCommandTest extends RocketeerTestCase
{
    public function testProperlyNamespacesCommands()
    {
        $this->usesLaravel(true);
        $command = new InstallCommand();
        $command->setContainer($this->app);
        $this->assertEquals('deploy:plugin-install', $command->getName());

        $this->usesLaravel(false);
        $command = new InstallCommand();
        $command->setContainer($this->app);
        $this->assertEquals('plugin:install', $command->getName());
    }

    public function testGetsProperStatusCodeFromPipelines()
    {
        $this->mock(LogsHandler::class, 'LogsHandler', function (MockInterface $mock) {
            return $mock->shouldReceive('write')->andReturn([]);
        });

        $this->app->add('credentials.gatherer', Mockery::mock('CredentialsGatherer')->shouldIgnoreMissing());

        $command = new DummyFailingCommand();
        $command->setContainer($this->app);
        $code = $command->run(new ArrayInput([]), new NullOutput());

        $this->assertEquals(1, $code);
    }

    public function testDisplaysWarningInNonInteractiveMode()
    {
        $command = new DummyFailingCommand();
        $command->setContainer($this->app);

        $tester = $this->executeCommand($command, [], ['interactive' => false]);

        $this->assertContains('prompt was skipped: No password or SSH key', $tester->getDisplay());
    }

    public function testCanFireEvents()
    {
        $this->rocketeer->setLocal(true);
        $this->expectFiredEvent('commands.nope.before');

        $this->executeCommand(new DummyFailingCommand());
    }
}
