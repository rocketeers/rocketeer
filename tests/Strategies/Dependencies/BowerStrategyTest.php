<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Strategies\Dependencies;

use Mockery\MockInterface;
use Rocketeer\Binaries\PackageManagers\Bower;
use Rocketeer\TestCases\RocketeerTestCase;

class BowerStrategyTest extends RocketeerTestCase
{
    /**
     * @type \Rocketeer\Strategies\Dependencies\BowerStrategy
     */
    protected $bower;

    public function setUp()
    {
        parent::setUp();

        $bower = new Bower($this->app);
        $bower->setBinary('bower');

        $this->bower = $this->builder->buildStrategy('Dependencies', 'Bower');
        $this->bower->setManager($bower);
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
        $this->mockFiles(function (MockInterface $mock) {
            return $mock
                ->shouldReceive('has')->with($this->paths->getUserHomeFolder().'/.bowerrc')->andReturn(true)
                ->shouldReceive('read')->once()->andReturn('{"directory": "components"}');
        });

        $bower = $this->builder->buildBinary('Bower');
        $this->assertEquals('components', $bower->getDependenciesFolder());

        $this->mockFiles(function (MockInterface $mock) {
            return $mock->shouldReceive('has')->andReturn(false);
        });

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
