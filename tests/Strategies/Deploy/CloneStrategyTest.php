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

use Rocketeer\Binaries\Scm\ScmInterface;
use Rocketeer\Binaries\Scm\Svn;
use Rocketeer\TestCases\RocketeerTestCase;

class CloneStrategyTest extends RocketeerTestCase
{
    public function testCanDeployRepository()
    {
        $task = $this->pretendTask('Deploy');
        $task->getStrategy('Deploy')->deploy();

        $matcher = [
            'git clone "{repository}" "{server}/releases/{release}" --branch="master" --depth="1"',
            [
                'cd {server}/releases/{release}',
                'git submodule update --init --recursive',
            ],
        ];

        $this->assertHistory($matcher);
    }

    public function testCanUpdateRepository()
    {
        $task = $this->pretendTask('Deploy');
        $task->getStrategy('Deploy')->update();

        $matcher = [
            [
                "cd $this->server/releases/20000000000000",
                'git reset --hard',
                'git pull --recurse-submodules',
            ],
        ];

        $this->assertHistory($matcher);
    }

    public function testDoesntRunSubmodulesCheckoutForSvn()
    {
        $this->container->add(ScmInterface::class, new Svn($this->container));

        $task = $this->pretendTask('Deploy');
        $task->getStrategy('Deploy')->deploy();

        $matcher = [
            'svn co {repository}/master {server}/releases/{release} --non-interactive',
        ];

        $this->assertHistory($matcher);
    }
}
