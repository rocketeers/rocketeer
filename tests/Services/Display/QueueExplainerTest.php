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

namespace Rocketeer\Services\Display;

use Rocketeer\TestCases\RocketeerTestCase;

class QueueExplainerTest extends RocketeerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockEchoingCommand();
    }

    public function testDoesNotDisplayHandleIfOnlyOneConnection()
    {
        $this->config->set('connections', [
            'production' => [],
        ]);

        $this->expectExplainerOutput('|  ├─ foobar');

        $this->explainer->line('foobar');
    }

    public function testDoesNotDisplayHandleIfOnlyOneStage()
    {
        $this->config->set('connections', [
            'production' => [],
        ]);
        $this->config->set('stages.stages', ['staging']);

        $this->expectExplainerOutput('|  ├─ foobar');

        $this->explainer->line('foobar');
    }

    public function testDisplayHandleIfMultipleStages()
    {
        $this->config->set('stages.stages', ['staging', 'production']);
        $this->config->set('connections', ['production' => []]);

        $this->expectExplainerOutput('**       production       ** |  ├─ foobar');

        $this->explainer->line('foobar');
    }

    public function testDisplayHandleIfMultipleServersButSingleConnectionAndStage()
    {
        $this->swapConnections([
            'production' => [
                'servers' => [
                    ['host' => 'foo.com'],
                    ['host' => 'bar.com'],
                ],
            ],
        ]);

        $this->expectExplainerOutput('**  production/foo.com  ** |  ├─ foobar');

        $this->explainer->line('foobar');
    }

    public function testCanDisplayBasicMessage()
    {
        $this->expectExplainerOutput('|  ├─ foobar');

        $this->explainer->line('foobar');
    }

    public function testCanDisplaySuccessMessage()
    {
        $this->expectExplainerOutput('|  ├─ <fg=green>foobar</fg=green>');

        $this->explainer->success('foobar');
    }

    public function testCanDisplayErrors()
    {
        $this->expectExplainerOutput('|  ├─ <error>foobar</error>');

        $this->explainer->error('foobar');
    }

    public function testCanDisplayThingsInSubsection()
    {
        $this->expectExplainerOutput(
            '|  ├─ foo'.
            '│  |  ├─ bar'.
            '|  ├─ foo'
        );

        $this->explainer->line('foo');
        $this->explainer->displayBelow(function () {
            $this->explainer->line('bar');
        });
        $this->explainer->line('foo');
    }

    public function testCanDisplayStatus()
    {
        $this->expectExplainerOutput(
            '├─ <info>foobar</info> <comment>(Foobar)</comment> fired by <info>Foo</info> [~0.5s]'
        );

        $this->explainer->display('foobar', 'Foobar', 'Foo', 0.5);
    }

    public function testCanAdaptToVariousLengths()
    {
        $this->swapConfig(['stages.stages' => ['foo', 'foobarbaz']]);

        $this->expectExplainerOutput([
            '**    production/foo    ** |  ├─ foobar',
            '** production/foobarbaz ** |  ├─ foobar',
        ]);

        $this->connections->setStage('foo');
        $this->explainer->line('foobar');
        echo PHP_EOL;

        $this->connections->setStage('foobarbaz');
        $this->explainer->line('foobar');
    }

    public function testProperlyIndentsAllLines()
    {
        $this->expectExplainerOutput([
            '|  ├─ <comment>[{username}@production]</comment> foo',
            '|  ├─ <comment>[{username}@production]</comment> bar',
        ]);

        $this->explainer->server([
            'foo',
            'bar',
        ]);
    }

    /**
     * @param string|string[] $output
     */
    protected function expectExplainerOutput($output)
    {
        $output = (array) $output;
        $output = implode(PHP_EOL, $output);
        $output = preg_replace('/\*\*(.+)\*\*/', '<bg=magenta;options=bold>$1</bg=magenta;options=bold>', $output);

        $this->expectOutputString($output);
    }
}
