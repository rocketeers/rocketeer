<?php
namespace Strategies\Dependencies;

use Rocketeer\TestCases\RocketeerTestCase;

class BundlerStrategyTest extends RocketeerTestCase
{
	public function testCanInstallDependencies()
	{
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
