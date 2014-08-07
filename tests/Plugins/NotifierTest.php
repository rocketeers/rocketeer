<?php
namespace Rocketeer\Plugins;

use Rocketeer\Dummies\DummyNotifier;
use Rocketeer\TestCases\RocketeerTestCase;

class NotifierTest extends RocketeerTestCase
{
	public function setUp()
	{
		parent::setUp();

		$this->swapConfig(array(
			'rocketeer::stages.stages' => array('staging', 'production'),
			'rocketeer::hooks'         => array(),
			'rocketeer::connections'   => array(
				'production' => array(
					'host' => 'foo.bar.com'
				),
			),
		));
		$this->tasks->registerConfiguredEvents();

		$this->notifier = new DummyNotifier($this->app);
		$this->tasks->plugin($this->notifier);
	}

	public function testCanAppendStageToDetails()
	{
		$this->expectOutputString('Jean Eude finished deploying branch "master" on "staging@production" (foo.bar.com)');
		$this->localStorage->set('notifier.name', 'Jean Eude');
		$this->connections->setStage('staging');
		$this->notifier = new DummyNotifier($this->app);
		$this->tasks->plugin($this->notifier);

		$this->task('Deploy')->fireEvent('after');
	}

	public function testCanSendDeploymentsNotifications()
	{
		$this->expectOutputString('Jean Eude finished deploying branch "master" on "production" (foo.bar.com)');
		$this->localStorage->set('notifier.name', 'Jean Eude');

		$this->task('Deploy')->fireEvent('after');
	}

	public function testDoesntSendNotificationsInPretendMode()
	{
		$this->expectOutputString('');
		$this->localStorage->set('notifier.name', 'Jean Eude');

		$this->pretendTask('Deploy')->fireEvent('after');
	}
}
