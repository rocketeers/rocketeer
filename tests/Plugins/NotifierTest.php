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
		$this->app['rocketeer.tasks']->registerConfiguredEvents();

		$this->notifier = new DummyNotifier($this->app);
		$this->app['rocketeer.tasks']->plugin($this->notifier);
	}

	public function testCanAppendStageToDetails()
	{
		$this->expectOutputString('Jean Eude finished deploying branch "master" on "staging@production" (foo.bar.com)');
		$this->app['rocketeer.server']->setValue('notifier.name', 'Jean Eude');
		$this->app['rocketeer.rocketeer']->setStage('staging');
		$this->notifier = new DummyNotifier($this->app);
		$this->app['rocketeer.tasks']->plugin($this->notifier);

		$this->task('Deploy')->fireEvent('after');
	}

	public function testCanSendDeploymentsNotifications()
	{
		$this->expectOutputString('Jean Eude finished deploying branch "master" on "production" (foo.bar.com)');
		$this->app['rocketeer.server']->setValue('notifier.name', 'Jean Eude');

		$this->task('Deploy')->fireEvent('after');
	}

	public function testDoesntSendNotificationsInPretendMode()
	{
		$this->expectOutputString('');
		$this->app['rocketeer.server']->setValue('notifier.name', 'Jean Eude');

		$this->pretendTask('Deploy')->fireEvent('after');
	}
}
