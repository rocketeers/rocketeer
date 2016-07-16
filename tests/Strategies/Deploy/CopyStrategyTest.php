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

namespace Rocketeer\Strategies\Deploy;

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
        $this->builder->buildStrategy('Deploy', 'Copy')->deploy();

        $matcher = [
            'cp -a {server}/releases/10000000000000 {server}/releases/20000000000000',
            [
                'cd {server}/releases/{release}',
                'git reset --hard',
                'git pull',
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

        $this->builder->buildStrategy('Deploy', 'Copy')->deploy();

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

        $this->builder->buildStrategy('Deploy', 'Copy')->deploy();

        $matcher = [
            'git clone "{repository}" "{server}/releases/{release}" --branch="master" --depth="1"',
            [
                'cd {server}/releases/{release}',
                'git submodule update --init --recursive',
            ],
        ];

        $this->assertHistory($matcher);
    }
}
