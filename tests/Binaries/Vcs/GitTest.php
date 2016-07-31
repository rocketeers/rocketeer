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

namespace Rocketeer\Vcs;

use Rocketeer\Binaries\Vcs\Git;
use Rocketeer\TestCases\RocketeerTestCase;

class GitTest extends RocketeerTestCase
{
    /**
     * The current VCS instance.
     *
     * @var Git
     */
    protected $vcs;

    public function setUp()
    {
        parent::setUp();

        $this->vcs = new Git($this->container);
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// TESTS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    public function testCanGetCheck()
    {
        $command = $this->vcs->check();

        $this->assertEquals('git --version', $command);
    }

    public function testCanGetCurrentState()
    {
        $command = $this->vcs->currentState();

        $this->assertEquals('git rev-parse HEAD', $command);
    }

    public function testCanGetCurrentBranch()
    {
        $command = $this->vcs->currentBranch();

        $this->assertEquals('git rev-parse --abbrev-ref HEAD', $command);
    }

    public function testCanGetCheckout()
    {
        $this->swapVcsConfiguration([
            'shallow' => true,
            'repository' => 'http://github.com/my/repository',
            'branch' => 'develop',
        ]);

        $command = $this->vcs->checkout($this->server);

        $this->assertEquals('git clone "http://github.com/my/repository" "'.$this->server.'" --branch="develop" --depth="1"', $command);
    }

    public function testCanGetDeepClone()
    {
        $this->config->set('vcs.shallow', false);
        $this->swapVcsConfiguration([
            'repository' => 'http://github.com/my/repository',
            'branch' => 'develop',
        ]);

        $command = $this->vcs->checkout($this->server);

        $this->assertEquals('git clone "http://github.com/my/repository" "'.$this->server.'" --branch="develop"', $command);
    }

    public function testCanGetReset()
    {
        $command = $this->vcs->reset();

        $this->assertEquals('git reset --hard', $command);
    }

    public function testCanGetUpdate()
    {
        $command = $this->vcs->update();

        $this->assertEquals('git pull --recurse-submodules', $command);
    }

    public function testCanGetSubmodules()
    {
        $command = $this->vcs->submodules();

        $this->assertEquals('git submodule update --init --recursive', $command);
    }
}
