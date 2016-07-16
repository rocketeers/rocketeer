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

use Prophecy\Argument;
use Rocketeer\Binaries\PackageManagers\Composer;
use Rocketeer\Binaries\Scm\Git;
use Rocketeer\Services\Connections\Shell\Bash;
use Rocketeer\Services\Releases\ReleasesManager;
use Rocketeer\TestCases\RocketeerTestCase;

class AbstractBinaryTest extends RocketeerTestCase
{
    public function testCanExecuteMethod()
    {
        /** @var Bash $prophecy */
        $prophecy = $this->bindProphecy(Bash::class);
        $prophecy->run(Argument::cetera())->shouldBeCalledTimes(1)->will(function ($arguments) {
            return $arguments;
        });

        $scm = new Git($this->container);
        $command = $scm->run('checkout', $this->server);
        $expected = $this->replaceHistoryPlaceholders(['git clone "{repository}" "{server}" --branch="master" --depth="1"']);

        $this->assertEquals($expected, $command);
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

    public function testPathResolvingIsDelayedUntilNeeded()
    {
        $this->pretend();
        $prophecy = $this->bindProphecy(ReleasesManager::class);

        $binary = new Composer($this->container);
        $prophecy->getCurrentReleasePath()->shouldNotHaveBeenCalled();

        $binary->run('--version');
        $binary->run('--version');
        $prophecy->getCurrentReleasePath()->shouldHaveBeenCalledTimes(1);
    }

    public function testSettingBinaryManuallyMarksItAsResolved()
    {
        $this->pretend();
        $prophecy = $this->bindProphecy(ReleasesManager::class);

        $binary = new Composer($this->container);
        $binary->setBinary('foobar');
        $binary->run('--version');
        $prophecy->getCurrentReleasePath()->shouldNotHaveBeenCalled();
    }
}
