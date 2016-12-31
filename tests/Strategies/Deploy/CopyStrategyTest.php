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

namespace Rocketeer\Strategies\CreateRelease;

use Prophecy\Argument;
use Rocketeer\Services\Releases\ReleasesManager;
use Rocketeer\TestCases\RocketeerTestCase;

class CopyStrategyTest extends RocketeerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->pretend();
    }

    public function testCanCopyPreviousRelease()
    {
        $this->builder->buildStrategy('CreateRelease', 'Copy')->deploy();

        $matcher = [
            'cp -a {server}/releases/10000000000000 {server}/releases/20000000000000',
            [
                'cd {server}/releases/{release}',
                'git reset --hard',
                'git pull --recurse-submodules',
            ],
        ];

        $this->assertHistory($matcher);
    }

    public function testClonesIfNoPreviousRelease()
    {
        /** @var ReleasesManager $prophecy */
        $prophecy = $this->bindProphecy(ReleasesManager::class);
        $prophecy->getReleases()->willReturn([]);
        $prophecy->getCurrentReleasePath(Argument::any())->willReturn($this->server.'/releases/10000000000000');

        $this->builder->buildStrategy('CreateRelease', 'Copy')->deploy();

        $matcher = [
            'git clone "{repository}" "{server}/releases/{release}" --branch="master" --depth="1"',
            [
                'cd {server}/releases/{release}',
                'git submodule update --init --recursive',
            ],
        ];

        $this->assertHistory($matcher);
    }

    public function testCanCloneIfPreviousReleaseIsInvalid()
    {
        /** @var ReleasesManager $prophecy */
        $prophecy = $this->bindProphecy(ReleasesManager::class);
        $prophecy->getReleases()->willReturn([10000000000000]);
        $prophecy->getPreviousRelease()->willReturn(null);
        $prophecy->getPathToRelease(Argument::any())->willReturn(null);
        $prophecy->getCurrentReleasePath()->willReturn($this->server.'/releases/10000000000000');

        $this->builder->buildStrategy('CreateRelease', 'Copy')->deploy();

        $matcher = [
            'git clone "{repository}" "{server}/releases/{release}" --branch="master" --depth="1"',
            [
                'cd {server}/releases/{release}',
                'git submodule update --init --recursive',
            ],
        ];

        $this->assertHistory($matcher);
    }

    public function testCanProperlyCopyOnMultipleServers()
    {
        $root = str_replace('foobar', null, $this->server);
        $this->swapConfigWithEvents([
            'hooks' => [],
            'connections' => [
                'production' => [
                    'servers' => [
                        ['host' => 'foo.com', 'root_directory' => $root],
                        ['host' => 'bar.com', 'root_directory' => $root.'foobar'],
                    ],
                ],
            ],
            'vcs.submodules' => false,
            'remote.permissions.files' => [],
            'strategies.create-release' => 'Copy',
            'strategies.dependencies' => null,
        ]);

        $this->queue->run('deploy');

        $this->assertHistoryContains('cp -a {server}/releases/20000000000000 {server}/releases/{release}');
        $this->assertHistoryContains('git clone "{repository}" "{server}/foobar/releases/{release}" --branch="master" --depth="1"');
    }
}
