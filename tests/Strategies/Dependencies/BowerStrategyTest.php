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

use Rocketeer\Binaries\PackageManagers\Bower;
use Rocketeer\TestCases\RocketeerTestCase;

class BowerStrategyTest extends RocketeerTestCase
{
    /**
     * @var \Rocketeer\Strategies\Dependencies\BowerStrategy
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
        $this->mock('rocketeer.connections', 'Connections', function ($mock) {
            return $mock->shouldReceive('getServerCredentials')->andReturn(['username' => 'root']);
        });

        $this->pretend();
        $this->bower->install();

        $this->assertHistory([
            [
                'cd {server}/releases/{release}',
                'bower install --allow-root',
            ],
        ]);
    }
}
