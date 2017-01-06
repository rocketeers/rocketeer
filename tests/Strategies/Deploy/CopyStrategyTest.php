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

use Mockery\MockInterface;
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
        $this->mock('rocketeer.releases', 'ReleasesManager', function (MockInterface $mock) {
            return $mock->shouldReceive('getReleases')->andReturn([])
                        ->shouldReceive('getCurrentReleasePath')->andReturn($this->server.'/releases/10000000000000');
        });

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
        $this->mock('rocketeer.releases', 'ReleasesManager', function (MockInterface $mock) {
            return $mock->shouldReceive('getReleases')->andReturn([10000000000000])
                        ->shouldReceive('getPreviousRelease')->andReturn(null)
                        ->shouldReceive('getPathToRelease')->andReturn(null)
                        ->shouldReceive('getCurrentReleasePath')->andReturn($this->server.'/releases/10000000000000');
        });

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
