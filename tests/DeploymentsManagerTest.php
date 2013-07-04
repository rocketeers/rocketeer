<?php
include '_start.php';

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
		$this->assertEquals('bar', $this->app['rocketeer.server']->getValue('foo'));
	}

	public function testCanSetValueInDeploymentsFile()
	{
		$this->app['rocketeer.server']->setValue('foo', 'baz');

		$this->assertEquals('baz', $this->app['rocketeer.server']->getValue('foo'));
	}

	public function testCandeleteRepository()
	{
		$this->app['rocketeer.server']->deleteRepository();

		$this->assertFalse($this->app['files']->exists(__DIR__.'/meta/deployments.json'));
	}

	public function testCanFallbackIfFileDoesntExist()
	{
		$this->app['rocketeer.server']->deleteRepository();

		$this->assertEquals(null, $this->app['rocketeer.server']->getValue('foo'));
	}

}
