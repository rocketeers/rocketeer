<?php
namespace Rocketeer\Scm;

use Rocketeer\TestCases\RocketeerTestCase;

class SvnTest extends RocketeerTestCase
{
	/**
	 * The current SCM instance
	 *
	 * @var Git
	 */
	protected $scm;

	public function setUp()
	{
		parent::setUp();

		$this->scm = new Svn($this->app);
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// TESTS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	public function testCanGetCheck()
	{
		$command = $this->scm->check();

		$this->assertEquals('svn --version', $command);
	}

	public function testCanGetCurrentState()
	{
		$command = $this->scm->currentState();

		$this->assertEquals('svn info -r "HEAD" | grep "Revision"', $command);
	}

	public function testCanGetCurrentBranch()
	{
		$command = $this->scm->currentBranch();

		$this->assertEquals('echo trunk', $command);
	}

	public function testCanGetCheckout()
	{
		$this->mock('rocketeer.connections', 'ConnectionsHandler', function ($mock) {
			return $mock
				->shouldReceive('getRepositoryCredentials')->once()->andReturn(['username' => 'foo', 'password' => 'bar'])
				->shouldReceive('getRepositoryEndpoint')->once()->andReturn('http://github.com/my/repository')
				->shouldReceive('getRepositoryBranch')->once()->andReturn('develop');
		});

		$command = $this->scm->checkout($this->server);

		$this->assertEquals('svn co http://github.com/my/repository/develop '.$this->server.' --non-interactive --username="foo" --password="bar"', $command);
	}

	public function testCanGetDeepClone()
	{
		$this->mock('rocketeer.connections', 'ConnectionsHandler', function ($mock) {
			return $mock
				->shouldReceive('getRepositoryCredentials')->once()->andReturn(['username' => 'foo', 'password' => 'bar'])
				->shouldReceive('getRepositoryEndpoint')->once()->andReturn('http://github.com/my/repository')
				->shouldReceive('getRepositoryBranch')->once()->andReturn('develop');
		});

		$command = $this->scm->checkout($this->server);

		$this->assertEquals('svn co http://github.com/my/repository/develop '.$this->server.' --non-interactive --username="foo" --password="bar"', $command);
	}

	public function testCanGetReset()
	{
		$command = $this->scm->reset();

		$this->assertEquals("svn status -q | grep -v '^[~XI ]' | awk '{print $2;}' | xargs svn revert", $command);
	}

	public function testCanGetUpdate()
	{
		$command = $this->scm->update();

		$this->assertEquals('svn up --non-interactive', $command);
	}

	public function testCanGetSubmodules()
	{
		$command = $this->scm->submodules();

		$this->assertEmpty($command);
	}
}
