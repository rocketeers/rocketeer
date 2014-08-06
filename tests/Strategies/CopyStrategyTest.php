<?php
namespace Strategies;

use Mockery\MockInterface;
use Rocketeer\Strategies\CopyStrategy;
use Rocketeer\TestCases\RocketeerTestCase;

class CopyStrategyTest extends RocketeerTestCase
{
	public function setUp()
	{
		parent::setUp();

		$this->app->bind('rocketeer.strategy', function ($app) {
			return new CopyStrategy($app);
		});

		$this->pretend();
	}

	public function testCanCopyPreviousRelease()
	{
		$this->strategy->deploy();

		$matcher = array(
			'cp -r {server}/releases/10000000000000 {server}/releases/20000000000000',
			array(
				"cd {server}/releases/{release}",
				"git reset --hard",
				"git pull",
			),
		);

		$this->assertHistory($matcher, $this->history->getFlattened());
	}

	public function testClonesIfNoPreviousRelease()
	{
		$this->mock('rocketeer.releases', 'ReleasesManager', function (MockInterface $mock) {
			return $mock->shouldReceive('getReleases')->andReturn([])
			            ->shouldReceive('getCurrentReleasePath')->andReturn($this->server.'/releases/10000000000000');
		});

		$this->strategy->deploy();

		$matcher = array(
			'git clone --depth 1 -b master "{repository}" {server}/releases/{release}',
			array(
				"cd {server}/releases/{release}",
				"git submodule update --init --recursive"
			),
		);

		$this->assertHistory($matcher, $this->history->getFlattened());
	}

	public function testCanCloneIfPreviousReleaseIsInvalid()
	{
		$this->mock('rocketeer.releases', 'ReleasesManager', function (MockInterface $mock) {
			return $mock->shouldReceive('getReleases')->andReturn([10000000000000])
			            ->shouldReceive('getPreviousRelease')->andReturn(null)
			            ->shouldReceive('getPathToRelease')->andReturn(null)
			            ->shouldReceive('getCurrentReleasePath')->andReturn($this->server.'/releases/10000000000000');
		});

		$this->strategy->deploy();

		$matcher = array(
			'git clone --depth 1 -b master "{repository}" {server}/releases/{release}',
			array(
				"cd {server}/releases/{release}",
				"git submodule update --init --recursive"
			),
		);

		$this->assertHistory($matcher, $this->history->getFlattened());
	}
}
