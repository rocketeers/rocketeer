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

		$this->queue->on('production', ['cleanup'], $this->getCommand());
		$this->assertEquals(['foobar'], $this->history->getFlattenedOutput());
	}
}
