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

namespace Rocketeer\Services\Connections\Shell\Modules;

use Rocketeer\Dummies\Tasks\DummyShelledTask;
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

        $this->mockEchoingCommand();
        $this->connections->getCurrentConnection()->setPreviousStatus(1);

        $status = $this->task('Deploy')->displayStatusMessage('Oh noes', 'git clone');
        $this->assertFalse($status);
    }

    public function testCheckStatusReturnsTrueSuccessful()
    {
        $this->assertTrue($this->pretendTask()->displayStatusMessage('Oh noes'));
    }

    public function testCanGetTimestampOffServer()
    {
        $timestamp = $this->task->getTimestamp();

        $this->assertEquals(date('YmdHis'), $timestamp);
    }

    public function testCanGetLocalTimestampIfError()
    {
        $this->bindDummyConnection('NOPE');
        $timestamp = $this->task->getTimestamp();

        $this->assertEquals(date('YmdHis'), $timestamp);
    }

    public function testCanLetFrameworkProcessCommands()
    {
        $this->usesLaravel();

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
        $this->bindDummyConnection(['npm --version' => 'Inappropriate ioctl for device'.PHP_EOL.'1.2.3']);

        $result = $this->bash->run('npm --version');
        $this->assertEquals('1.2.3', $result);
    }

    public function testCanConvertDirectorySeparators()
    {
        $this->swapConfig([
            'remote.variables.directory_separator' => '\\',
        ]);

        $commands = 'cd C:/_bar?/12baz';
        $processed = $this->task->processCommands($commands);

        $this->assertEquals(['cd C:\_bar?\12baz'], $processed);
    }

    public function testDoesntConvertSlashesThatArentDirectorySeparators()
    {
        $this->swapConfig([
            'remote.variables.directory_separator' => '\\',
        ]);

        $commands = 'find runtime -name "cache" -follow -exec rm -rf "{}" '.DS.';';
        $processed = $this->task->processCommands($commands);

        $this->assertEquals([$commands], $processed);
    }

    public function testShowsRawCommandsIfVerboseEnough()
    {
        $this->expectOutputString('<fg=magenta>$ ls</fg=magenta>');

        $prophecy = $this->mockEchoingCommand();
        $prophecy->getVerbosity()->willReturn(OutputInterface::VERBOSITY_VERY_VERBOSE);

        $this->bash->runRaw('ls');
    }

    public function testDoesntShowRawCommandsIfVerbosityNotHighEnough()
    {
        $this->expectOutputString('');

        $prophecy = $this->mockEchoingCommand();
        $prophecy->getVerbosity()->willReturn(OutputInterface::VERBOSITY_NORMAL);

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

        $this->task->on('local', function () {
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

    public function testCanShellAllCommandByDefault()
    {
        $this->assertTaskHistory(DummyShelledTask::class, [
            [
                'bash --login -c \'echo "foo"\'',
                'bash --login -c \'echo "bar"\'',
            ],
        ]);
    }

    public function testCanExecuteCommandsOnSpecificConnection()
    {
        $this->assertEquals('production', $this->task->getConnection()->getConnectionKey()->name);

        $this->bash->on('staging', function ($task) {
            $this->assertEquals('staging', $task->getConnection()->getConnectionKey()->name);
        });

        $this->assertEquals('production', $this->task->getConnection()->getConnectionKey()->name);
    }
}
