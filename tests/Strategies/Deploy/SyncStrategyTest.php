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

		$this->assertRsyncHistory(null, array(
			'mkdir {server}/releases/{release}',
		));
	}

	public function testCanUpdateRepository()
	{
		$task = $this->pretendTask('Deploy');
		$task->getStrategy('Deploy', 'Sync')->update();

		$this->assertRsyncHistory();
	}

	public function testCanSpecifyPortViaHostname()
	{
		$this->swapConfig(array(
			'rocketeer::connections' => array(
				'production' => array(
					'host'     => 'bar.com:12345',
					'username' => 'foo',
				),
			),
		));

		$task = $this->pretendTask('Deploy');
		$task->getStrategy('Deploy', 'Sync')->update();

		$this->assertRsyncHistory(12345);
	}

	public function testCanSpecifyPortViaOptions()
	{
		$task = $this->pretendTask('Deploy');
		$task->getStrategy('Deploy', 'Sync', ['port' => 12345])->update();

		$this->assertRsyncHistory(12345);
	}

	protected function assertRsyncHistory($port = null, $prepend = [])
	{
		$port    = $port ? ' -p '.$port : null;
		$matcher = array_merge($prepend, array(
			'rsync ./ foo@bar.com:{server}/releases/{release} --verbose --recursive --rsh="ssh'.$port.'" --exclude=".git" --exclude="vendor"',
		));

		$this->assertHistory($matcher);
	}
}
