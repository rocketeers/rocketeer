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

use Rocketeer\Binaries\Vcs\Svn;
use Rocketeer\TestCases\RocketeerTestCase;

class SvnTest extends RocketeerTestCase
{
    /**
     * The current VCS instance.
     *
     * @var Svn
     */
    protected $vcs;

    public function setUp()
    {
        parent::setUp();

        $this->vcs = new Svn($this->container);
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// TESTS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    public function testCanGetCheck()
    {
        $command = $this->vcs->check();

        $this->assertEquals('svn --version', $command);
    }

    public function testCanGetCurrentState()
    {
        $command = $this->vcs->currentState();

        $this->assertEquals('svn info --show-item revision', $command);
    }

    public function testCanGetCurrentEndpoint()
    {
        $command = $this->vcs->currentEndpoint();

        $this->assertEquals('svn info --show-item url', $command);
    }

    public function testCanGetCurrentBranch()
    {
        $command = $this->vcs->currentBranch();

        $this->assertEquals('echo trunk', $command);
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

        $this->assertEquals('svn co http://github.com/my/repository/develop '.$this->server.' --non-interactive --username="foo" --password="bar"', $command);
    }

    public function testCanGetDeepClone()
    {
        $this->swapVcsConfiguration([
            'username' => 'foo',
            'password' => 'bar',
            'repository' => 'http://github.com/my/repository',
            'branch' => 'develop',
        ]);

        $command = $this->vcs->checkout($this->server);

        $this->assertEquals('svn co http://github.com/my/repository/develop '.$this->server.' --non-interactive --username="foo" --password="bar"', $command);
    }

    public function testDoesNotDuplicateCredentials()
    {
        $this->swapVcsConfiguration([
            'username' => 'foo',
            'password' => 'bar',
            'repository' => 'http://foo:bar@github.com/my/repository',
            'branch' => 'develop',
        ]);

        $command = $this->vcs->checkout($this->server);

        $this->assertEquals('svn co http://github.com/my/repository/develop '.$this->server.' --non-interactive --username="foo" --password="bar"', $command);

        $this->swapVcsConfiguration([
            'username' => 'foo',
            'password' => null,
            'repository' => 'http://foo@github.com/my/repository',
            'branch' => 'develop',
        ]);

        $command = $this->vcs->checkout($this->server);

        $this->assertEquals('svn co http://github.com/my/repository/develop '.$this->server.' --non-interactive --username="foo"', $command);
    }

    public function testDoesNotStripRevisionFromUrl()
    {
        $this->swapVcsConfiguration([
            'username' => 'foo',
            'password' => 'bar',
            'repository' => 'url://user:login@example.com/test',
            'branch' => 'trunk@1234',
        ]);

        $command = $this->vcs->checkout($this->server);

        $this->assertEquals('svn co url://example.com/test/trunk@1234 '.$this->server.' --non-interactive --username="foo" --password="bar"', $command);
    }

    public function testCanGetReset()
    {
        $command = $this->vcs->reset();

        $this->assertEquals("svn status -q | grep -v '^[~XI ]' | awk '{print $2;}' | xargs --no-run-if-empty svn revert", $command);
    }

    public function testCanGetUpdate()
    {
        $command = $this->vcs->update();

        $this->assertEquals('svn up --non-interactive', $command);
    }

    public function testCanGetSubmodules()
    {
        $command = $this->vcs->submodules();

        $this->assertEmpty($command);
    }
}
