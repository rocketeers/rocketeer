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

use Illuminate\Support\Arr;
use Prophecy\Argument;
use Rocketeer\Services\Releases\ReleasesManager;
use Rocketeer\TestCases\RocketeerTestCase;

class SetupTest extends RocketeerTestCase
{
    public function testCanSetupServer()
    {
        $this->usesComposer();
        $this->mockNoCurrentRelease();

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
        $this->usesComposer();
        $this->mockNoCurrentRelease();
        $this->swapConfig([
            'stages.stages' => ['staging', 'production'],
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
        $this->usesComposer(true, 'staging');
        $this->mockNoCurrentRelease('staging');
        $this->swapConfig([
            'stages.stages' => ['staging', 'production'],
        ]);

        $this->connections->setStage('staging');
        $this->assertEquals('staging', $this->connections->getCurrentConnectionKey()->stage);
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

        $this->assertEquals('staging', $this->connections->getCurrentConnectionKey()->stage);
    }

    protected function mockNoCurrentRelease($stage = null)
    {
        $server = $this->server;

        /** @var ReleasesManager $prophecy */
        $prophecy = $this->bindProphecy(ReleasesManager::class);
        $prophecy->getCurrentRelease()->willReturn();
        $prophecy->getCurrentReleasePath(Argument::any())->will(function ($arguments) use ($server, $stage) {
            $stage = $stage ? $stage.'/' : null;

            return $server.'/'.$stage.'releases/20000000000000/'.Arr::get($arguments, 0);
        });
    }
}
