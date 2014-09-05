<?php
namespace Rocketeer\Services\Connections;

use Rocketeer\TestCases\RocketeerTestCase;

class ConnectionsHandlerTest extends RocketeerTestCase
{
	////////////////////////////////////////////////////////////////////
	//////////////////////////////// TESTS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	public function testCanGetAvailableConnections()
	{
		$connections = $this->connections->getAvailableConnections();
		$this->assertEquals(array('production', 'staging'), array_keys($connections));

		$this->app['rocketeer.storage.local']->set('connections.custom.username', 'foobar');
		$connections = $this->connections->getAvailableConnections();
		$this->assertEquals(array('production', 'staging', 'custom'), array_keys($connections));
	}

	public function testCanGetCurrentConnection()
	{
		$this->swapConfig(array('rocketeer::default' => 'foobar'));
		$this->assertConnectionEquals('production');

		$this->swapConfig(array('rocketeer::default' => 'production'));
		$this->assertConnectionEquals('production');

		$this->swapConfig(array('rocketeer::default' => 'staging'));
		$this->assertConnectionEquals('staging');
	}

	public function testCanChangeConnection()
	{
		$this->assertConnectionEquals('production');

		$this->connections->setConnection('staging');
		$this->assertConnectionEquals('staging');

		$this->connections->setConnections('staging,production');
		$this->assertEquals(array('staging', 'production'), $this->connections->getConnections());
	}

	public function testCanUseSshRepository()
	{
		$repository = 'git@github.com:'.$this->repository;
		$this->expectRepositoryConfig($repository, '', '');

		$this->assertRepositoryEquals($repository);
	}

	public function testCanUseHttpsRepository()
	{
		$this->expectRepositoryConfig('https://github.com/'.$this->repository, 'foobar', 'bar');

		$this->assertRepositoryEquals('https://foobar:bar@github.com/'.$this->repository);
	}

	public function testCanUseHttpsRepositoryWithUsernameProvided()
	{
		$this->expectRepositoryConfig('https://foobar@github.com/'.$this->repository, 'foobar', 'bar');

		$this->assertRepositoryEquals('https://foobar:bar@github.com/'.$this->repository);
	}

	public function testCanUseHttpsRepositoryWithOnlyUsernameProvided()
	{
		$this->expectRepositoryConfig('https://foobar@github.com/'.$this->repository, 'foobar', '');

		$this->assertRepositoryEquals('https://foobar@github.com/'.$this->repository);
	}

	public function testCanCleanupProvidedRepositoryFromCredentials()
	{
		$this->expectRepositoryConfig('https://foobar@github.com/'.$this->repository, 'Anahkiasen', '');

		$this->assertRepositoryEquals('https://Anahkiasen@github.com/'.$this->repository);
	}

	public function testCanUseHttpsRepositoryWithoutCredentials()
	{
		$this->expectRepositoryConfig('https://github.com/'.$this->repository, '', '');

		$this->assertRepositoryEquals('https://github.com/'.$this->repository);
	}

	public function testCanCheckIfRepositoryNeedsCredentials()
	{
		$this->expectRepositoryConfig('https://github.com/'.$this->repository, '', '');
		$this->assertTrue($this->connections->needsCredentials());
	}

	public function testCangetRepositoryBranch()
	{
		$this->assertEquals('master', $this->connections->getRepositoryBranch());
	}

	public function testFillsConnectionCredentialsHoles()
	{
		$connections = $this->connections->getAvailableConnections();
		$this->assertArrayHasKey('production', $connections);

		$this->app['rocketeer.storage.local']->set('connections', array(
			'staging' => array(
				'host'      => 'foobar',
				'username'  => 'user',
				'password'  => '',
				'keyphrase' => '',
				'key'       => '/Users/user/.ssh/id_rsa',
				'agent'     => '',
			),
		));
		$connections = $this->connections->getAvailableConnections();
		$this->assertArrayHasKey('production', $connections);
	}

	public function testCanCreateHandleForCurrent()
	{
		$handle = $this->connections->getHandle('foo', 2, 'staging');

		$this->assertEquals('foo/2/staging', $handle);
	}

	public function testDoesntDisplayServerNumberIfNotMultiserver()
	{
		$handle = $this->connections->getHandle('foo', 0, 'staging');

		$this->assertEquals('foo/staging', $handle);
	}

	public function testDoesntResetConnectionIfSameAsCurrent()
	{
		$this->mock('rocketeer.tasks', 'TasksHandler', function ($mock) {
			return $mock
				->shouldReceive('registerConfiguredEvents')->once();
		}, false);

		$this->connections->setConnection('production');
		$this->connections->setConnection('production');
		$this->connections->setConnection('production');
	}

	public function testDoesntResetStageIfSameAsCurrent()
	{
		$this->mock('rocketeer.tasks', 'TasksHandler', function ($mock) {
			return $mock
				->shouldReceive('registerConfiguredEvents')->once();
		}, false);

		$this->connections->setStage('foobar');
		$this->connections->setStage('foobar');
		$this->connections->setStage('foobar');
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// HELPERS ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Make the config return specific SCM config
	 *
	 * @param string $repository
	 * @param string $username
	 * @param string $password
	 *
	 * @return void
	 */
	protected function expectRepositoryConfig($repository, $username, $password)
	{
		$this->swapConfig(array(
			'rocketeer::scm' => array(
				'repository' => $repository,
				'username'   => $username,
				'password'   => $password,
			),
		));
	}
}
