<?php
namespace Strategies\Dependencies;

use Rocketeer\TestCases\RocketeerTestCase;

class BundlerStrategyTest extends RocketeerTestCase
{
	public function testCanInstallDependencies()
	{
		$bundler = $this->pretendTask()->getStrategy('Dependencies', 'Bundler', true);
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
		$bundler = $this->pretendTask()->getStrategy('Dependencies', 'Bundler', true);
		$bundler->update();

		$this->assertHistory(array(
			array(
				'cd {server}/releases/{release}',
				'bundle update',
			),
		));
	}
}
