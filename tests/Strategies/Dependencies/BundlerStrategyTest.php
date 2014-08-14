<?php
namespace Rocketeer\Strategies\Dependencies;

use Rocketeer\TestCases\RocketeerTestCase;

class BundlerStrategyTest extends RocketeerTestCase
{
	public function testCanInstallDependencies()
	{
		$this->pretend();
		$bundler = $this->builder->buildStrategy('Dependencies', 'Bundler');
		$bundler->install();

		$this->assertHistory(array(
			array(
				'cd {server}/releases/{release}',
				'bundle install',
			),
		));
	}

	public function testCanUpdateDependencies()
	{
		$this->pretend();
		$bundler = $this->builder->buildStrategy('Dependencies', 'Bundler');
		$bundler->update();

		$this->assertHistory(array(
			array(
				'cd {server}/releases/{release}',
				'bundle update',
			),
		));
	}
}
