<?php
namespace Rocketeer\Strategies\Dependencies;

use Rocketeer\Binaries\PackageManagers\Bower;
use Rocketeer\TestCases\RocketeerTestCase;

class BowerStrategyTest extends RocketeerTestCase
{
	/**
	 * @type \Rocketeer\Strategies\Dependencies\BowerStrategy
	 */
	protected $bower;

	public function setUp()
	{
		parent::setUp();

		$bower = new Bower($this->app);
		$bower->setBinary('bower');

		$this->bower = $this->builder->buildStrategy('Dependencies', 'Bower');
		$this->bower->setManager($bower);
	}

	public function testCanInstallDependencies()
	{
		$this->pretend();
		$this->bower->install();

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
		$this->bower->update();

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
		$this->bower->install();

		$this->assertHistory(array(
			array(
				'cd {server}/releases/{release}',
				'bower install --allow-root',
			),
		));
	}

	public function testCanShareDependenciesFolder()
	{
		$this->mockFiles(function ($mock) {
			return $mock->shouldReceive('exists')->with($this->paths->getUserHomeFolder().'/.bowerrc')->andReturn(true);
		});

		$this->mock('rocketeer.bash', 'Bash', function ($mock) {
			return $mock->shouldReceive('share')->once();
		});

		$this->pretend();
		$this->bower->configure(['shared_dependencies' => true]);
		$this->bower->install();
	}
}
