<?php

class IgniteTest extends RocketeerTests
{
	public function testCanIgniteConfigurationOutsideLaravel()
	{
		$this->app['artisan'] = null;
		$this->app->offsetUnset('artisan');

		$this->app['path.base'] = __DIR__.'/../..';

		// Execute Task
		$task = $this->task('Ignite');
		$task->execute();

		$root = $this->app['path.base'].'/rocketeer.php';
		$this->assertFileExists($root);

		$config = include $this->app['path.base'].'/src/config/config.php';
		$contents = include $root;
		$this->assertEquals($config, $contents);
	}

	public function testCanIgniteConfigurationInLaravel()
	{
		$this->app['path.base'] = __DIR__.'/../..';
		$root = $this->app['path.base'].'/rocketeer.php';

		$command = $this->getCommand();
		$command->shouldReceive('call')->with('config:publish', array('package' => 'anahkiasen/rocketeer'))->andReturnUsing(function () use ($root) {
			file_put_contents($root, 'foobar');
		});

		$task = $this->task('Ignite', $command);
		$task->execute();

		$contents = file_get_contents($root);
		$this->assertEquals('foobar', $contents);
	}
}
