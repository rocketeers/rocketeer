<?php
namespace Rocketeer\Tests\Tasks;

use Rocketeer\Tests\TestCases\RocketeerTestCase;

class IgniteTest extends RocketeerTestCase
{
	public function testCanIgniteConfigurationOutsideLaravel()
	{
		$this->app['artisan'] = null;
		$this->app->offsetUnset('artisan');

		$this->app['path.base'] = realpath($this->server.'/../../..');
		$this->app['path.rocketeer.config'] = $this->app['path.base'].'/rocketeer';

		// Execute Task
		$task = $this->task('Ignite');
		$task->execute();

		$root = $this->app['path.rocketeer.config'];
		$this->assertFileExists($root);

		$config   = include $this->app['path.base'].'/src/config/config.php';
		$contents = include $root.'/config.php';
		$this->assertEquals(array('production'), $contents['default']);
	}

	public function testCanIgniteConfigurationInLaravel()
	{
		$this->app['path.base'] = realpath($this->server.'/../../..');

		$this->app['files']->deleteDirectory($this->app['path.rocketeer.config']);
		$this->app['files']->makeDirectory($this->app['path.rocketeer.config']);
		$root = $this->app['path.rocketeer.config'].'/config.php';

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
