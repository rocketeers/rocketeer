<?php
namespace Rocketeer\Strategies\Deploy;

use Mockery\MockInterface;
use Rocketeer\TestCases\RocketeerTestCase;

class CopyStrategyTest extends RocketeerTestCase
{
	public function setUp()
	{
		parent::setUp();

		$this->pretend();
	}

	public function testCanCopyPreviousRelease()
	{
		$this->builder->buildStrategy('Deploy', 'Copy')->deploy();

		$matcher = array(
			'cp -r {server}/releases/10000000000000 {server}/releases/20000000000000',
			array(
				"cd {server}/releases/{release}",
				"git reset --hard",
				"git pull",
			),
		);

		$this->assertHistory($matcher);
	}

	public function testClonesIfNoPreviousRelease()
	{
		$this->mock('rocketeer.releases', 'ReleasesManager', function (MockInterface $mock) {
			return $mock->shouldReceive('getReleases')->andReturn([])
			            ->shouldReceive('getCurrentReleasePath')->andReturn($this->server.'/releases/10000000000000');
		});

		$this->builder->buildStrategy('Deploy', 'Copy')->deploy();

		$matcher = array(
			'git clone "{repository}" "{server}/releases/{release}" --branch="master" --depth="1"',
			array(
				"cd {server}/releases/{release}",
				"git submodule update --init --recursive",
			),
		);

		$this->assertHistory($matcher);
	}

	public function testCanCloneIfPreviousReleaseIsInvalid()
	{
		$this->mock('rocketeer.releases', 'ReleasesManager', function (MockInterface $mock) {
			return $mock->shouldReceive('getReleases')->andReturn([10000000000000])
			            ->shouldReceive('getPreviousRelease')->andReturn(null)
			            ->shouldReceive('getPathToRelease')->andReturn(null)
			            ->shouldReceive('getCurrentReleasePath')->andReturn($this->server.'/releases/10000000000000');
		});

		$this->builder->buildStrategy('Deploy', 'Copy')->deploy();

		$matcher = array(
			'git clone "{repository}" "{server}/releases/{release}" --branch="master" --depth="1"',
			array(
				"cd {server}/releases/{release}",
				"git submodule update --init --recursive",
			),
		);

		$this->assertHistory($matcher);
	}
}
