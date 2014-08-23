<?php
namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\Dummies\DummyBeforeAfterNotifier;
use Rocketeer\TestCases\RocketeerTestCase;

class NotifyTest extends RocketeerTestCase
{
	public function testDoesntSendTheSameNotificationTwice()
	{
		$this->swapConfig(array(
			'rocketeer::hooks' => array(),
		));

		$this->tasks->plugin(new DummyBeforeAfterNotifier($this->app));

		$this->expectOutputString('before_deployafter_deploy');
		$this->localStorage->set('notifier.name', 'Jean Eude');

		$this->task('Deploy')->fireEvent('before');
		$this->task('Deploy')->fireEvent('after');
	}
}
