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

namespace Rocketeer\Binaries;

use Mockery\MockInterface;
use Rocketeer\Binaries\Scm\Git;
use Rocketeer\Services\Connections\Shell\Bash;
use Rocketeer\TestCases\RocketeerTestCase;

class AbstractBinaryTest extends RocketeerTestCase
{
    public function testCanExecuteMethod()
    {
        $this->mock(Bash::class, 'Bash', function (MockInterface $mock) {
            return $mock->shouldReceive('run')->once()->withAnyArgs()->andReturnUsing(function ($arguments) {
                return $arguments;
            });
        });

        $scm = new Git($this->container);
        $command = $scm->run('checkout', $this->server);
        $expected = $this->replaceHistoryPlaceholders(['git clone "{repository}" "{server}" --branch="master" --depth="1"']);

        $this->assertEquals($expected[0], $command);
    }

    public function testCanProperlyBuildMultivalueOptions()
    {
        $binary = new Git($this->container);
        $command = $binary->getCommand('foobar', [], ['--foo' => ['bar', 'baz']]);

        $this->assertEquals('git foobar --foo="bar" --foo="baz"', $command);
    }

    public function testCanBuildOptinsIfNoKeysSpecified()
    {
        $binary = new Git($this->container);
        $command = $binary->getCommand('foobar', [], ['--foo', '--bar']);

        $this->assertEquals('git foobar --foo --bar', $command);
    }

    public function testCanBuildOptinsIfNoValuesSpecified()
    {
        $binary = new Git($this->container);
        $command = $binary->getCommand('foobar', [], ['--foo' => 'lol', '--bar']);

        $this->assertEquals('git foobar --foo="lol" --bar', $command);
    }
}
