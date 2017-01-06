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

        $this->scm = new Git($this->app);
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
        $this->mock('rocketeer.rocketeer', 'Rocketeer\Rocketeer', function ($mock) {
            return $mock->shouldReceive('getOption')->once()->with('scm.shallow')->andReturn(true);
        });
        $this->mock('rocketeer.connections', 'ConnectionsHandler', function ($mock) {
            return $mock
                ->shouldReceive('getRepositoryEndpoint')->once()->andReturn('http://github.com/my/repository')
                ->shouldReceive('getRepositoryBranch')->once()->andReturn('develop');
        });

        $command = $this->scm->checkout($this->server);

        $this->assertEquals('git clone "http://github.com/my/repository" "'.$this->server.'" --branch="develop" --depth="1"', $command);
    }

    public function testCanGetDeepClone()
    {
        $this->mock('rocketeer.rocketeer', 'Rocketeer\Rocketeer', function ($mock) {
            return $mock->shouldReceive('getOption')->once()->with('scm.shallow')->andReturn(false);
        });
        $this->mock('rocketeer.connections', 'ConnectionsHandler', function ($mock) {
            return $mock
                ->shouldReceive('getRepositoryEndpoint')->once()->andReturn('http://github.com/my/repository')
                ->shouldReceive('getRepositoryBranch')->once()->andReturn('develop');
        });

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

        $this->assertEquals('git pull', $command);
    }

    public function testCanGetSubmodules()
    {
        $command = $this->scm->submodules();

        $this->assertEquals('git submodule update --init --recursive', $command);
    }
}
