<?php
include '_start.php';

class DeploymentsManagerTest extends RocketeerTests
{

	public function testCanGetValueFromDeploymentsFile()
	{
		$this->assertEquals('bar', $this->app['rocketeer.deployments']->getValue('foo'));
	}

	public function testCanSetValueInDeploymentsFile()
	{
		$this->markTestSkipped('To redo with an actual Filesystem instance');

		$this->app['rocketeer.deployments']->setValue('foo', 'baz');

		$this->assertEquals('baz', $this->app['rocketeer.deployments']->getValue('foo'));
	}

}