<?php
namespace Rocketeer\Scm;

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
}
