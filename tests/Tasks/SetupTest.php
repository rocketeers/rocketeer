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

namespace Rocketeer\Tasks;

use Rocketeer\TestCases\RocketeerTestCase;

class SetupTest extends RocketeerTestCase
{
    public function testCanSetupServer()
    {
        $this->pretend();

        $this->mockReleases(function ($mock) {
            return $mock
                ->shouldReceive('getCurrentRelease')->andReturn(null)
                ->shouldReceive('getCurrentReleasePath')->andReturn('1');
        });

        $this->assertTaskHistory('Setup', [
            'git --version',
            '{php} -m',
            'mkdir {server}/',
            'mkdir -p {server}/releases',
            'mkdir -p {server}/current',
            'mkdir -p {server}/shared',
        ]);
    }

    public function testCanSetupStages()
    {
        $this->pretend();

        $this->mockReleases(function ($mock) {
            return $mock
                ->shouldReceive('getCurrentRelease')->andReturn(null)
                ->shouldReceive('getCurrentReleasePath')->andReturn('1');
        });
        $this->swapConfig([
            'rocketeer::stages.stages' => ['staging', 'production'],
        ]);

        $this->assertTaskHistory('Setup', [
            'git --version',
            '{php} -m',
            'mkdir {server}/',
            'mkdir -p {server}/staging/releases',
            'mkdir -p {server}/staging/current',
            'mkdir -p {server}/staging/shared',
            'mkdir -p {server}/production/releases',
            'mkdir -p {server}/production/current',
            'mkdir -p {server}/production/shared',
        ]);
    }

    public function testRunningSetupKeepsCurrentConfiguredStage()
    {
        $this->pretend();

        $this->mockReleases(function ($mock) {
            return $mock
                ->shouldReceive('getCurrentRelease')->andReturn(null)
                ->shouldReceive('getCurrentReleasePath')->andReturn('1');
        });
        $this->swapConfig([
            'rocketeer::stages.stages' => ['staging', 'production'],
        ]);

        $this->connections->setStage('staging');
        $this->assertEquals('staging', $this->connections->getStage());
        $this->assertTaskHistory('Setup', [
            'git --version',
            '{php} -m',
            'mkdir {server}/',
            'mkdir -p {server}/staging/releases',
            'mkdir -p {server}/staging/current',
            'mkdir -p {server}/staging/shared',
            'mkdir -p {server}/production/releases',
            'mkdir -p {server}/production/current',
            'mkdir -p {server}/production/shared',
        ], [
            'stage' => 'staging',
        ]);
        $this->assertEquals('staging', $this->connections->getStage());
    }
}
