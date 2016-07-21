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

use Rocketeer\Binaries\Scm\Hg;
use Rocketeer\TestCases\RocketeerTestCase;

class HgTest extends RocketeerTestCase
{
    /**
     * The current SCM instance.
     *
     * @var Hg
     */
    protected $scm;

    public function setUp()
    {
        parent::setUp();

        $this->scm = new Hg($this->container);
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// TESTS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    public function testCanGetCheck()
    {
        $command = $this->scm->check();

        $this->assertEquals('hg --version', $command);
    }

    public function testCanGetCurrentState()
    {
        $command = $this->scm->currentState();

        $this->assertEquals('hg identify -i', $command);
    }

    public function testCanGetCurrentBranch()
    {
        $command = $this->scm->currentBranch();

        $this->assertEquals('hg branch', $command);
    }

    public function testCanGetCheckout()
    {
        $this->swapRepositoryCredentials([
            'username' => 'foo',
            'password' => 'bar',
            'repository' => 'http://github.com/my/repository',
            'branch' => 'develop',
        ]);

        $command = $this->scm->checkout($this->server);

        $this->assertEquals('hg clone "http://github.com/my/repository" -b develop "'.$this->server.'" --config ui.interactive="no" --config auth.x.prefix="http://" --config auth.x.username="foo" --config auth.x.password="bar"', $command);
    }

    public function testCanGetReset()
    {
        $command = $this->scm->reset();

        $this->assertEquals('hg update --clean', $command);
    }

    public function testCanGetUpdate()
    {
        $command = $this->scm->update();

        $this->assertEquals('hg pull', $command);
    }

    public function testCanGetSubmodules()
    {
        $command = $this->scm->submodules();

        $this->assertEmpty($command);
    }
}
