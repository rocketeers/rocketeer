<?php
namespace Rocketeer\Services\Connections;

use Rocketeer\TestCases\RocketeerTestCase;

class RemoteHandlerTest extends RocketeerTestCase
{
	/**
	 * @type RemoteHandler
	 */
	protected $handler;

	public function setUp()
	{
		parent::setUp();

		$this->handler = new RemoteHandler($this->app);
		unset($this->app['rocketeer.command']);
	}

	public function testCanCreateConnection()
	{
		$this->swapConfig(array(
			'rocketeer::connections' => array(
				'production' => array(
					'host'     => 'foobar.com',
					'username' => 'foobar',
					'password' => 'foobar',
				),
			),
		));

		$connection = $this->handler->connection();

		$this->assertInstanceOf('Rocketeer\Services\Connections\Connection', $connection);
		$this->assertEquals('production', $connection->getName());
		$this->assertEquals('foobar', $connection->getUsername());
	}

	public function testThrowsExceptionIfMissingCredentials()
	{
		$this->setExpectedException('Rocketeer\Exceptions\MissingCredentialsException');

		$this->swapConfig(array(
			'rocketeer::connections' => array(
				'production' => array(
					'host'     => 'foobar.com',
					'username' => 'foobar',
				),
			),
		));

		$this->handler->connection();
	}

	public function testThrowsExceptionIfMissingInformations()
	{
		$this->setExpectedException('Rocketeer\Exceptions\MissingCredentialsException');

		$this->swapConfig(array(
			'rocketeer::connections' => array(
				'production' => array(
					'username' => 'foobar',
					'password' => 'foobar',
				),
			),
		));

		$this->handler->connection();
	}

	public function testCachesConnections()
	{
		$this->swapConfig(array(
			'rocketeer::connections' => array(
				'production' => array(
					'host'     => 'foobar.com',
					'username' => 'foobar',
					'password' => 'foobar',
				),
			),
		));

		$connection = $this->handler->connection();
		$this->assertInstanceOf('Rocketeer\Services\Connections\Connection', $connection);
		$this->assertEquals('production', $connection->getName());

		$this->swapConfig(array(
			'rocketeer::connections' => array(
				'production' => array(),
			),
		));

		$connection = $this->handler->connection();
		$this->assertInstanceOf('Rocketeer\Services\Connections\Connection', $connection);
		$this->assertEquals('production', $connection->getName());
	}

	public function testThrowsExceptionIfUnableToConnect()
	{
		$this->setExpectedException('Rocketeer\Exceptions\ConnectionException');

		$this->swapConfig(array(
			'rocketeer::connections' => array(
				'production' => array(
					'host'     => 'foobar.com',
					'username' => 'foobar',
					'password' => 'foobar',
				),
			),
		));

		$this->handler->run('ls');
	}
}
