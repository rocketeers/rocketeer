<?php
namespace Rocketeer\Strategies\Deploy;

use Rocketeer\TestCases\RocketeerTestCase;

class CloneStrategyTest extends RocketeerTestCase
{
	public function testCanDeployRepository()
	{
		$task = $this->pretendTask('Deploy');
		$task->getStrategy('Deploy')->deploy();

		$matcher = array(
			'git clone "{repository}" "{server}/releases/{release}" --branch="master" --depth="1"',
			array(
				"cd {server}/releases/{release}",
				"git submodule update --init --recursive",
			),
		);

		$this->assertHistory($matcher);
	}

	public function testCanUpdateRepository()
	{
		$task = $this->pretendTask('Deploy');
		$task->getStrategy('Deploy')->update();

		$matcher = array(
			array(
				"cd $this->server/releases/20000000000000",
				"git reset --hard",
				"git pull",
			),
		);

		$this->assertHistory($matcher);
	}
}
