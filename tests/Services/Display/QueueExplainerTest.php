<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\Display;

use Mockery\MockInterface;
use Rocketeer\TestCases\RocketeerTestCase;

class QueueExplainerTest extends RocketeerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mock('rocketeer.command', 'Command', function (MockInterface $mock) {
            return $mock->shouldReceive('line')->andReturnUsing(function ($input) {
                echo $input;
            });
        });
    }

    public function testDoesntDisplayHandleIfOnlyOneConnection()
    {
        $this->config->set('connections', [
            'production' => [],
        ]);

        $this->expectOutputString('|=> foobar');

        $this->explainer->line('foobar');
    }

    public function testDoesntDisplayHandleIfOnlyOneStage()
    {
        $this->config->set('connections', [
            'production' => [],
        ]);
        $this->config->set('stages.stages', ['staging']);

        $this->expectOutputString('|=> foobar');

        $this->explainer->line('foobar');
    }

    public function testDisplayHandleIfMultipleStages()
    {
        $this->config->set('connections', [
            'production' => [],
        ]);
        $this->config->set('stages.stages', ['staging', 'production']);

        $this->expectOutputString('<fg=cyan>production</fg=cyan>             |=> foobar');

        $this->explainer->line('foobar');
    }

    public function testDisplayHandleIfMultipleServersButSingleConnectionAndStage()
    {
        $this->swapConnections([
            'production' => [
                'servers' => [
                    ['host' => 'foo.com'],
                    ['host' => 'bar.com'],
                ]
            ],
        ]);

        $this->expectOutputString('<fg=cyan>production/foo.com</fg=cyan> |=> foobar');

        $this->explainer->line('foobar');
    }

    public function testCanDisplayBasicMessage()
    {
        $this->expectOutputString('<fg=cyan>production</fg=cyan> |=> foobar');

        $this->explainer->line('foobar');
    }

    public function testCanDisplaySuccessMessage()
    {
        $this->expectOutputString('<fg=cyan>production</fg=cyan> |=> <fg=green>foobar</fg=green>');

        $this->explainer->success('foobar');
    }

    public function testCanDisplayErrors()
    {
        $this->expectOutputString('<fg=cyan>production</fg=cyan> |=> <error>foobar</error>');

        $this->explainer->error('foobar');
    }

    public function testCanDisplayThingsInSubsection()
    {
        $this->expectOutputString(
            '<fg=cyan>production</fg=cyan> |=> foo'.
            '<fg=cyan>production</fg=cyan> |===> bar'.
            '<fg=cyan>production</fg=cyan> |=> foo'
        );

        $this->explainer->line('foo');
        $this->explainer->displayBelow(function () {
            $this->explainer->line('bar');
        });
        $this->explainer->line('foo');
    }

    public function testCanDisplayStatus()
    {
        $this->expectOutputString(
            '<fg=cyan>production</fg=cyan> | '.
            '<info>foobar</info> <comment>(Foobar)</comment> fired by <info>Foo</info> [~0.5s]'
        );

        $this->explainer->display('foobar', 'Foobar', 'Foo', 0.5);
    }

    public function testCanAdaptToVariousLengths()
    {
        $this->config->set('stages.stages', ['foo', 'foobarbaz']);

        $this->expectOutputString(
            '<fg=cyan>production/foo</fg=cyan>        |=> foobar'.
            '<fg=cyan>production/foobarbaz</fg=cyan>  |=> foobar'
        );

        $this->connections->setStage('foo');
        $this->explainer->line('foobar');

        $this->connections->setStage('foobarbaz');
        $this->explainer->line('foobar');
    }
}
