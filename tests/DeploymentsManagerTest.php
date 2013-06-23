<?php
class DeploymentsManagerTest extends RocketeerTests
{

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// SETUP /////////////////////////////
	////////////////////////////////////////////////////////////////////

	public function tearDown()
	{
		$deployments = array('foo' => 'bar', 'current_release' => 2000000000);
		$this->app['files']->put(__DIR__.'/meta/deployments.json', json_encode($deployments));
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// TESTS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	public function testCanGetValueFromDeploymentsFile()
	{
		$this->assertEquals('bar', $this->app['rocketeer.deployments']->getValue('foo'));
	}

	public function testCanSetValueInDeploymentsFile()
	{
		$this->app['rocketeer.deployments']->setValue('foo', 'baz');

		$this->assertEquals('baz', $this->app['rocketeer.deployments']->getValue('foo'));
	}

	public function testCanDeleteDeploymentsFile()
	{
		$this->app['rocketeer.deployments']->deleteDeploymentsFile();

		$this->assertFalse($this->app['files']->exists(__DIR__.'/meta/deployments.json'));
	}

	public function testCanFallbackIfFileDoesntExist()
	{
		$this->app['rocketeer.deployments']->deleteDeploymentsFile();

		$this->assertEquals(null, $this->app['rocketeer.deployments']->getValue('foo'));
	}

}