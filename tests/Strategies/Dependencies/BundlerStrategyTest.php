<?php
namespace Rocketeer\Strategies\Dependencies;

use Rocketeer\Binaries\PackageManagers\Bundler;
use Rocketeer\TestCases\RocketeerTestCase;

class BundlerStrategyTest extends RocketeerTestCase
{
	protected $bundler;

	public function setUp()
	{
		parent::setUp();

		$bundler = new Bundler($this->app);
		$bundler->setBinary('bundle');

		$this->bundler = $this->builder->buildStrategy('Dependencies', 'Bundler');
		$this->bundler->setManager($bundler);
	}

	public function testCanInstallDependencies()
	{
		$this->pretend();
		$this->bundler->install();

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
		$this->bundler->update();

		$this->assertHistory(array(
			array(
				'cd {server}/releases/{release}',
				'bundle update',
			),
		));
	}
}
