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

namespace Rocketeer\Traits\BashModules;

use Rocketeer\TestCases\RocketeerTestCase;

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

        $this->app['rocketeer.remote'] = clone $this->getRemote()->shouldReceive('status')->andReturn(1)->mock();
        $this->mockCommand([], [
            'line' => function ($error) {
                echo $error;
            },
        ]);

        $status = $this->task('Deploy')->checkStatus('Oh noes', 'git clone');

        $this->assertFalse($status);
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

    public function testDoesntAppendEnvironmentToStandardTasks()
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
        $this->mockRemote('stdin: is not a tty'.PHP_EOL.'something');
        $result = $this->bash->run('ls');

        $this->assertEquals('something', $result);
    }

    public function testCanRunCommandsLocally()
    {
        $this->mock('rocketeer.remote', 'Remote', function ($mock) {
            return $mock->shouldReceive('run')->never();
        });

        $this->task->setLocal(true);
        $contents = $this->task->runRaw('ls', true, true);

        $this->assertListDirectory($contents);
    }

    public function testCanConvertDirectorySeparators()
    {
        $this->mockConfig([
            'rocketeer::remote.variables.directory_separator' => '\\',
        ]);

        $commands = 'cd C:/_bar?/12baz';
        $processed = $this->task->processCommands($commands);

        $this->assertEquals(['cd C:\_bar?\12baz'], $processed);
    }

    public function testDoesntConvertSlashesThatArentDirectorySeparators()
    {
        $this->mockConfig([
            'rocketeer::remote.variables.directory_separator' => '\\',
        ]);

        $commands = 'find runtime -name "cache" -follow -exec rm -rf "{}" '.DS.';';
        $processed = $this->task->processCommands($commands);

        $this->assertEquals([$commands], $processed);
    }

    public function testCanExecuteCommandsAsSudo()
    {
        $this->swapConfig([
            'rocketeer::remote.sudo' => true,
            'rocketeer::remote.sudoed' => ['cd'],
        ]);

        $this->assertEquals(['sudo cd foobar'], $this->task->processCommands('cd foobar'));
    }

    public function testCanExecuteCommandsAsSudoUser()
    {
        $this->swapConfig([
            'rocketeer::remote.sudo' => 'foobar',
            'rocketeer::remote.sudoed' => ['cd'],
        ]);

        $this->assertEquals(['sudo -u foobar cd foobar'], $this->task->processCommands('cd foobar'));
    }
}
