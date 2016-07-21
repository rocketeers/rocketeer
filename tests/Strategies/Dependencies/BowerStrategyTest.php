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

namespace Rocketeer\Strategies\Dependencies;

use Prophecy\Argument;
use Rocketeer\Binaries\PackageManagers\Bower;
use Rocketeer\TestCases\RocketeerTestCase;

class BowerStrategyTest extends RocketeerTestCase
{
    /**
     * @var BowerStrategy
     */
    protected $bower;

    public function setUp()
    {
        parent::setUp();

        $bower = new Bower($this->container);
        $bower->setBinary('bower');

        $this->bower = $this->builder->buildStrategy('Dependencies', 'Bower');
        $this->bower->setBinary($bower);
    }

    public function testCanInstallDependencies()
    {
        $this->pretend();
        $this->bower->install();

        $this->assertHistory([
            [
                'cd {server}/releases/{release}',
                'bower install',
            ],
        ]);
    }

    public function testCanUpdateDependencies()
    {
        $this->pretend();
        $this->bower->update();

        $this->assertHistory([
            [
                'cd {server}/releases/{release}',
                'bower update',
            ],
        ]);
    }

    public function testUsesAllowRootIfRoot()
    {
        $this->swapConnections([
            'production' => [
                'username' => 'root',
            ],
        ]);

        $this->pretend();
        $this->bower->install();

        $this->assertHistory([
            [
                'cd {server}/releases/{release}',
                'bower install --allow-root',
            ],
        ]);
    }

    public function testCanGetDependenciesFolder()
    {
        $prophecy = $this->bindFilesystemProphecy();
        $prophecy->has(Argument::containingString('/.bowerrc'))->willReturn(true);
        $prophecy->read(Argument::cetera())->shouldBeCalled()->willReturn('{"directory": "components"}');

        $bower = $this->builder->buildBinary('Bower');
        $this->assertEquals('components', $bower->getDependenciesFolder());

        $prophecy = $this->bindFilesystemProphecy();
        $prophecy->has(Argument::containingString('/.bowerrc'))->willReturn(false);

        $bower = $this->builder->buildBinary('Bower');
        $this->assertEquals('bower_components', $bower->getDependenciesFolder());
    }

    public function testCanAddFlags()
    {
        $this->swapConnections([
            'production' => [
                'username' => 'root',
            ],
        ]);

        $this->pretend();
        $this->bower->setFlags(['install' => ['--foo' => 'bar']]);
        $this->bower->install();

        $this->assertHistory([
            [
                'cd {server}/releases/{release}',
                'bower install --foo="bar" --allow-root',
            ],
        ]);
    }
}
