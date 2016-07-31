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

use Rocketeer\Binaries\Vcs\Hg;
use Rocketeer\TestCases\RocketeerTestCase;

class HgTest extends RocketeerTestCase
{
    /**
     * The current VCS instance.
     *
     * @var Hg
     */
    protected $vcs;

    public function setUp()
    {
        parent::setUp();

        $this->vcs = new Hg($this->container);
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// TESTS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    public function testCanGetCheck()
    {
        $command = $this->vcs->check();

        $this->assertEquals('hg --version', $command);
    }

    public function testCanGetCurrentState()
    {
        $command = $this->vcs->currentState();

        $this->assertEquals('hg identify -i', $command);
    }

    public function testCanGetCurrentBranch()
    {
        $command = $this->vcs->currentBranch();

        $this->assertEquals('hg branch', $command);
    }

    public function testCanGetCheckout()
    {
        $this->swapVcsConfiguration([
            'username' => 'foo',
            'password' => 'bar',
            'repository' => 'http://github.com/my/repository',
            'branch' => 'develop',
        ]);

        $command = $this->vcs->checkout($this->server);

        $this->assertEquals('hg clone "http://github.com/my/repository" -b develop "'.$this->server.'" --config ui.interactive="no" --config auth.x.prefix="http://" --config auth.x.username="foo" --config auth.x.password="bar"', $command);
    }

    public function testCanGetReset()
    {
        $command = $this->vcs->reset();

        $this->assertEquals('hg update --clean', $command);
    }

    public function testCanGetUpdate()
    {
        $command = $this->vcs->update();

        $this->assertEquals('hg pull', $command);
    }

    public function testCanGetSubmodules()
    {
        $command = $this->vcs->submodules();

        $this->assertEmpty($command);
    }
}
