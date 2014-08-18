<?php
namespace Rocketeer\Strategies\Dependencies;

use Rocketeer\TestCases\RocketeerTestCase;

class BowerStrategyTest extends RocketeerTestCase
{
	public function testCanInstallDependencies()
	{
		$this->pretend();
		$bower = $this->builder->buildStrategy('Dependencies', 'Bower');
		$bower->install();

		$this->assertHistory(array(
			array(
				'cd {server}/releases/{release}',
				exec('which bower').' install',
			),
		));
	}

	public function testCanUpdateDependencies()
	{
		$this->pretend();
		$bower = $this->builder->buildStrategy('Dependencies', 'Bower');
		$bower->update();

		$this->assertHistory(array(
			array(
				'cd {server}/releases/{release}',
				exec('which bower').' update',
			),
		));
	}

	public function testUsesAllowRootIfRoot()
	{
		$this->mock('rocketeer.connections', 'Connections', function ($mock) {
			return $mock->shouldReceive('getServerCredentials')->andReturn(['username' => 'root']);
		});

		$this->pretend();
		$bower = $this->builder->buildStrategy('Dependencies', 'Bower');
		$bower->install();

		$this->assertHistory(array(
			array(
				'cd {server}/releases/{release}',
				exec('which bower').' install --allow-root',
			),
		));
	}
}
