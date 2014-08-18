<?php
namespace Rocketeer\Strategies\Deploy;

use Rocketeer\TestCases\RocketeerTestCase;

class SyncStrategyTest extends RocketeerTestCase
{
	public function setUp()
	{
		parent::setUp();

		$this->swapConfig(array(
			'rocketeer::connections' => array(
				'production' => array(
					'host'     => 'bar.com',
					'username' => 'foo',
				),
			),
		));
	}

	public function testCanDeployRepository()
	{
		$task = $this->pretendTask('Deploy');
		$task->getStrategy('Deploy', 'Sync')->deploy();

		$matcher = array(
			'mkdir {server}/releases/{release}',
			'rsync ./ foo@bar.com:{server}/releases/{release} --verbose --recursive --rsh="ssh" --exclude=".git" --exclude="vendor"',
		);

		$this->assertHistory($matcher);
	}

	public function testCanUpdateRepository()
	{
		$task = $this->pretendTask('Deploy');
		$task->getStrategy('Deploy', 'Sync')->update();

		$matcher = array(
			'rsync ./ foo@bar.com:{server}/releases/{release} --verbose --recursive --rsh="ssh" --exclude=".git" --exclude="vendor"',
		);

		$this->assertHistory($matcher);
	}
}
