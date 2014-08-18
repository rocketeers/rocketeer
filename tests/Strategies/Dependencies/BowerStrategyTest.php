<?php
namespace Rocketeer\Strategies\Dependencies;

use AspectMock\Test;
use Rocketeer\TestCases\RocketeerTestCase;

class BowerStrategyTest extends RocketeerTestCase
{
	public function setUp()
	{
		parent::setUp();

		$this->app['rocketeer.bash'] = Test::double($this->app['rocketeer.bash'], ['which' => 'bower']);
	}

	public function testCanInstallDependencies()
	{
		$this->pretend();
		$bower = $this->builder->buildStrategy('Dependencies', 'Bower');
		$bower->install();

		$this->assertHistory(array(
			array(
				'cd {server}/releases/{release}',
				'bower install',
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
				'bower update',
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
				'bower install --allow-root',
			),
		));
	}
}
