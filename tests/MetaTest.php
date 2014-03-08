<?php
namespace Rocketeer;

use Rocketeer\Dummies\MyCustomTask;
use Rocketeer\TestCases\RocketeerTestCase;

class MetaTest extends RocketeerTestCase
{
	public function testCanOverwriteTasksViaContainer()
	{
		$this->app->bind('rocketeer.tasks.cleanup', function ($app) {
			return new MyCustomTask($app);
		});

		$queue = $this->app['rocketeer.tasks']->on('production', array('cleanup'), $this->getCommand());
		$this->assertEquals(array('foobar'), $queue);
	}
}
