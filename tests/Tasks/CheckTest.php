<?php
namespace Rocketeer\Tests\Tasks;

use Rocketeer\Tests\TestCases\RocketeerTestCase;

class CheckTest extends RocketeerTestCase
{
	public function testCanDoBasicCheck()
	{
		$this->assertTaskHistory('Check', array(
			'git --version',
			$this->php. ' -r "print PHP_VERSION;"',
			$this->php. ' -m',
		));
	}

	public function testCanCheckPhpExtensions()
	{
		$this->swapConfig(array(
			'database.default' => 'sqlite',
			'cache.driver'     => 'redis',
			'session.driver'   => 'apc',
		));

		$this->assertTaskHistory('Check', array(
			'git --version',
			$this->php. ' -r "print PHP_VERSION;"',
			$this->php. ' -m',
		));
	}
}
