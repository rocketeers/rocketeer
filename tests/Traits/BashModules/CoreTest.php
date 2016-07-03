<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Traits\BashModules;

use Mockery\MockInterface;
use Prophecy\Argument;
use Rocketeer\Services\Connections\ConnectionsFactory;
use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;
use Rocketeer\TestCases\RocketeerTestCase;
use Symfony\Component\Console\Output\OutputInterface;

class CoreTest extends RocketeerTestCase
{
    public function testCanGetArraysFromRawCommands()
    {
        $contents = $this->task->runRaw('ls', true, true);

        $this->assertListDirectory($contents);
    }

    public function testCanCheckStatusOfACommand()
    {
        $this->expectOutputRegex('/.+An error occured: "Oh noes", while running:\ngit clone.+/');

        $prophecy = $this->bindProphecy(ConnectionsFactory::class);
        $prophecy->make(Argument::type(ConnectionKey::class))->willReturn(clone $this->getRemote()->shouldReceive('status')->andReturn(1)->mock());
        $prophecy->isConnected(Argument::type(ConnectionKey::class))->willReturn(true);

        $this->mockEchoingCommand();

        $status = $this->task('Deploy')->checkStatus('Oh noes', 'git clone');
        $this->assertFalse($status);
    }

    public function testCheckStatusReturnsTrueSuccessful()
    {
        $this->assertTrue($this->pretendTask()->checkStatus('Oh noes'));
    }

    public function testCanGetTimestampOffServer()
    {
        $timestamp = $this->task->getTimestamp();

        $this->assertEquals(date('YmdHis'), $timestamp);
    }

    public function testCanGetLocalTimestampIfError()
    {
        $this->mockRemote('NOPE');
        $timestamp = $this->task->getTimestamp();

        $this->assertEquals(date('YmdHis'), $timestamp);
    }

    public function testCanLetFrameworkProcessCommands()
    {
        $this->connections->setStage('staging');
        $commands = $this->pretendTask()->processCommands([
            'artisan something',
            'rm readme*',
        ]);

        $this->assertEquals([
            'artisan something --env="staging"',
            'rm readme*',
        ], $commands);
    }

    public function testCanRemoveCommonPollutingOutput()
    {
        $this->mockRemote(['npm --version' => 'Inappropriate ioctl for device'.PHP_EOL.'1.2.3']);

        $result = $this->bash->run('npm --version');
        $this->assertEquals('1.2.3', $result);
    }

    public function testCanRunCommandsLocally()
    {
        $this->mock(ConnectionsFactory::class, ConnectionsFactory::class, function (MockInterface $mock) {
            return $mock->shouldReceive('run')->never();
        });

        $this->task->setLocal(true);
        $contents = $this->task->runRaw('ls', true, true);

        $this->assertListDirectory($contents);
    }

    public function testCanConvertDirectorySeparators()
    {
        $this->mockConfig([
            'remote.variables.directory_separator' => '\\',
        ]);

        $commands = 'cd C:/_bar?/12baz';
        $processed = $this->task->processCommands($commands);

        $this->assertEquals(['cd C:\_bar?\12baz'], $processed);
    }

    public function testDoesntConvertSlashesThatArentDirectorySeparators()
    {
        $this->mockConfig([
            'remote.variables.directory_separator' => '\\',
        ]);

        $commands = 'find runtime -name "cache" -follow -exec rm -rf "{}" '.DS.';';
        $processed = $this->task->processCommands($commands);

        $this->assertEquals([$commands], $processed);
    }

    public function testShowsRawCommandsIfVerboseEnough()
    {
        $this->expectOutputString('<fg=magenta>$ ls</fg=magenta>');

        $this->mock('rocketeer.command', 'Command', function (MockInterface $mock) {
            return $mock
                ->shouldReceive('getVerbosity')->andReturn(OutputInterface::VERBOSITY_VERY_VERBOSE)
                ->shouldReceive('writeln')->andReturnUsing(function ($input) {
                    echo $input;
                });
        });

        $this->bash->runRaw('ls');
    }

    public function testDoesntShowRawCommandsIfVerbosityNotHighEnough()
    {
        $this->expectOutputString('');

        $this->mock('rocketeer.command', 'Command', function (MockInterface $mock) {
            return $mock
                ->shouldReceive('getVerbosity')->andReturn(OutputInterface::VERBOSITY_NORMAL)
                ->shouldReceive('writeln')->andReturnUsing(function ($input) {
                    echo $input;
                });
        });

        $this->bash->runRaw('ls');
    }

    public function testCanFlattenCommands()
    {
        $commands = $this->pretendTask()->processCommands([
            ['foo', 'bar'],
            'baz',
        ]);

        $this->assertEquals(['foo', 'bar', 'baz'], $commands);
    }

    public function testDoesntAffectGlobalLocalStateWhenUsingOnLocal()
    {
        $this->rocketeer->setLocal(true);
        $this->assertTrue($this->rocketeer->isLocal());

        $this->task()->onLocal(function () {
            // ...
        });

        $this->assertTrue($this->rocketeer->isLocal());
    }

    public function testCanExecuteCommandsAsSudo()
    {
        $this->swapConfig([
            'remote.sudo' => true,
            'remote.sudoed' => ['cd'],
        ]);

        $this->assertEquals(['sudo cd foobar'], $this->task->processCommands('cd foobar'));
    }

    public function testCanExecuteCommandsAsSudoUser()
    {
        $this->swapConfig([
            'remote.sudo' => 'foobar',
            'remote.sudoed' => ['cd'],
        ]);

        $this->assertEquals(['sudo -u foobar cd foobar'], $this->task->processCommands('cd foobar'));
    }
}
