<?php
namespace Rocketeer\Tasks;

use Rocketeer\TestCases\RocketeerTestCase;

class CheckTest extends RocketeerTestCase
{
	public function testCanCheckScmVersionIfRequired()
	{
		$this->assertTaskHistory('Check', array(
			'git --version',
			'{php} -m',
		));
	}

	public function testSkipsScmCheckIfNotRequired()
	{
		$this->swapConfig(array(
			'rocketeer::strategies.deploy' => 'sync',
		));

		$this->assertTaskHistory('Check', array(
			'{php} -m',
		));
	}
}
