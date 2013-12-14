<?php

class CheckTest extends RocketeerTests
{
	public function testCanDoBasicCheck()
	{
		$task = $this->pretendTask('Check');
		$task->fire();

		$matcher = array(
			'git --version',
			$this->php. ' -r "print PHP_VERSION;"',
			$this->php. ' -m',
		);

		$this->assertEquals($matcher, $task->getHistory());
	}

	public function testCanCheckPhpExtensions()
	{
		$this->swapConfig(array(
			'database.default' => 'sqlite',
			'cache.driver'     => 'redis',
			'session.driver'   => 'apc',
		));

		$task = $this->pretendTask('Check');
		$task->fire();

		$matcher = array(
			'git --version',
			$this->php. ' -r "print PHP_VERSION;"',
			$this->php. ' -m',
		);

		$this->assertEquals($matcher, $task->getHistory());
	}
}
