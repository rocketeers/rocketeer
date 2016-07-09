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

namespace Rocketeer\Console\Commands;

use Rocketeer\Console\Commands\Plugins\InstallCommand;
use Rocketeer\Dummies\DummyFailingCommand;
use Rocketeer\Services\Connections\Credentials\CredentialsGatherer;
use Rocketeer\TestCases\RocketeerTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class AbstractCommandTest extends RocketeerTestCase
{
    public function testGetsProperStatusCodeFromPipelines()
    {
        $this->bindProphecy(CredentialsGatherer::class);

        $command = new DummyFailingCommand();
        $command->setContainer($this->container);
        $code = $command->run(new ArrayInput([]), new NullOutput());

        $this->assertEquals(1, $code);
    }

    public function testDisplaysWarningInNonInteractiveMode()
    {
        $command = new DummyFailingCommand();
        $command->setContainer($this->container);

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
