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

namespace Rocketeer\Scm;

use Rocketeer\Binaries\Scm\Git;
use Rocketeer\TestCases\RocketeerTestCase;

class GitTest extends RocketeerTestCase
{
    /**
     * The current SCM instance.
     *
     * @var Git
     */
    protected $scm;

    public function setUp()
    {
        parent::setUp();

        $this->scm = new Git($this->container);
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// TESTS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    public function testCanGetCheck()
    {
        $command = $this->scm->check();

        $this->assertEquals('git --version', $command);
    }

    public function testCanGetCurrentState()
    {
        $command = $this->scm->currentState();

        $this->assertEquals('git rev-parse HEAD', $command);
    }

    public function testCanGetCurrentBranch()
    {
        $command = $this->scm->currentBranch();

        $this->assertEquals('git rev-parse --abbrev-ref HEAD', $command);
    }

    public function testCanGetCheckout()
    {
        $this->swapScmConfiguration([
            'shallow' => true,
            'repository' => 'http://github.com/my/repository',
            'branch' => 'develop',
        ]);

        $command = $this->scm->checkout($this->server);

        $this->assertEquals('git clone "http://github.com/my/repository" "'.$this->server.'" --branch="develop" --depth="1"', $command);
    }

    public function testCanGetDeepClone()
    {
        $this->config->set('scm.shallow', false);

        $this->swapScmConfiguration([
            'repository' => 'http://github.com/my/repository',
            'branch' => 'develop',
        ]);

        $command = $this->scm->checkout($this->server);

        $this->assertEquals('git clone "http://github.com/my/repository" "'.$this->server.'" --branch="develop"', $command);
    }

    public function testCanGetReset()
    {
        $command = $this->scm->reset();

        $this->assertEquals('git reset --hard', $command);
    }

    public function testCanGetUpdate()
    {
        $command = $this->scm->update();

        $this->assertEquals('git pull --recurse-submodules', $command);
    }

    public function testCanGetSubmodules()
    {
        $command = $this->scm->submodules();

        $this->assertEquals('git submodule update --init --recursive', $command);
    }
}
