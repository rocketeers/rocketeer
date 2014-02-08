<?php
namespace Rocketeer\Scm;

use Rocketeer\TestCases\RocketeerTestCase;

class GitTest extends RocketeerTestCase
{
	/**
	 * The current SCM instance
	 *
	 * @var Rocketeer\Scm\Git
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
		$this->mock('rocketeer.rocketeer', 'Rocketeer', function ($mock) {
			return $mock
				->shouldReceive('getOption')->once()->with('scm.shallow')->andReturn(true)
				->shouldReceive('getRepository')->once()->andReturn('http://github.com/my/repository')
				->shouldReceive('getRepositoryBranch')->once()->andReturn('develop');
		});

		$command = $this->scm->checkout($this->server);

		$this->assertEquals('git clone --depth 1 -b develop "http://github.com/my/repository" ' .$this->server, $command);
	}

	public function testCanGetDeepClone()
	{
		$this->mock('rocketeer.rocketeer', 'Rocketeer', function ($mock) {
			return $mock
				->shouldReceive('getOption')->once()->with('scm.shallow')->andReturn(false)
				->shouldReceive('getRepository')->once()->andReturn('http://github.com/my/repository')
				->shouldReceive('getRepositoryBranch')->once()->andReturn('develop');
		});

		$command = $this->scm->checkout($this->server);

		$this->assertEquals('git clone -b develop "http://github.com/my/repository" ' .$this->server, $command);
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
