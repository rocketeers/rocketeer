<?php
namespace Rocketeer\Abstracts;

use Mockery;
use Mockery\MockInterface;
use Rocketeer\Console\Commands\Plugins\InstallCommand;
use Rocketeer\Dummies\DummyFailingCommand;
use Rocketeer\TestCases\RocketeerTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class AbstractCommandTest extends RocketeerTestCase
{
    public function testProperlyNamespacesCommands()
    {
        $this->usesLaravel(true);
        $command = new InstallCommand();
        $command->setLaravel($this->app);
        $this->assertEquals('deploy:plugin-install', $command->getName());

        $this->usesLaravel(false);
        $command = new InstallCommand();
        $command->setLaravel($this->app);
        $this->assertEquals('plugin:install', $command->getName());
    }

    public function testGetsProperStatusCodeFromPipelines()
    {
        $this->mock('rocketeer.logs', 'LogsHandler', function (MockInterface $mock) {
            return $mock->shouldReceive('write')->andReturn([]);
        });
        $this->app['rocketeer.credentials'] = Mockery::mock('CredentialsGatherer')->shouldIgnoreMissing();

        $command = new DummyFailingCommand();
        $command->setLaravel($this->app);
        $code = $command->run(new ArrayInput([]), new NullOutput());

        $this->assertEquals(1, $code);
    }

    public function testDisplaysWarningInNonInteractiveMode()
    {
        $command = new DummyFailingCommand();
        $command->setLaravel($this->app);

        $tester = $this->executeCommand($command, [], ['interactive' => false]);

        $this->assertContains('prompt was skipped: No host is set for [production]', $tester->getDisplay());
    }

    public function testCanFireEvents()
    {
        $this->rocketeer->setLocal(true);
        $this->expectOutputString('foobar');

        $this->tasks->listenTo('commands.nope.before', function () {
            echo 'foobar';

            return false;
        });

        $this->executeCommand(new DummyFailingCommand());
    }
}
