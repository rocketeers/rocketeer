<?php
namespace Rocketeer\Scm;

use Mockery\MockInterface;
use Rocketeer\TestCases\RocketeerTestCase;

class HgTest extends RocketeerTestCase
{
    /**
     * The current SCM instance
     *
     * @var Hg
     */
    protected $scm;

    public function setUp()
    {
        parent::setUp();

        $this->scm = new Hg($this->app);
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
        $this->mock('rocketeer.connections', 'ConnectionsHandler', function (MockInterface $mock) {
            return $mock
                ->shouldReceive('getRepositoryCredentials')->once()->andReturn([
                    'username' => 'foo',
                    'password' => 'bar',
                ])
                ->shouldReceive('getRepositoryEndpoint')->once()->andReturn('http://github.com/my/repository')
                ->shouldReceive('getRepositoryBranch')->once()->andReturn('develop');
        });

        $command = $this->scm->checkout($this->server);

        $this->assertEquals('hg clone "http://github.com/my/repository" -b develop "' .$this->server. '" --config ui.interactive="no" --config auth.x.prefix="http://" --config auth.x.username="foo" --config auth.x.password="bar"', $command);
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
