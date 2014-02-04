<?php
namespace Rocketeer\Tasks;

use Rocketeer\TestCases\RocketeerTestCase;

class IgniteTest extends RocketeerTestCase
{
	public function testCanIgniteConfigurationOutsideLaravel()
	{
		$command = $this->getCommand(array('ask' => 'foobar'));

		$server = $this->server;
		$this->mock('rocketeer.igniter', 'Igniter', function ($mock) use ($server) {
			return $mock
				->shouldReceive('getConfigurationPath')->twice()
				->shouldReceive('exportConfiguration')->once()->andReturn($server)
				->shouldReceive('updateConfiguration')->once()->with($server, array(
					'scm_repository'   => '',
					'scm_username'     => '',
					'scm_password'     => '',
					'application_name' => 'foobar',
				));
		});

		$this->assertTaskOutput('Ignite', 'Rocketeer configuration was created', $command);
	}

	public function testCanIgniteConfigurationInLaravel()
	{
		$command = $this->getCommand(array('isInsideLaravel' => true));
		$command->shouldReceive('call')->with('config:publish', array('package' => 'anahkiasen/rocketeer'))->andReturn('foobar');

		$path = $this->app['path'].'/config/packages/anahkiasen/rocketeer';
		$this->mock('rocketeer.igniter', 'Igniter', function ($mock) use ($path) {
			return $mock
				->shouldReceive('getConfigurationPath')->twice()
				->shouldReceive('exportConfiguration')->never()
				->shouldReceive('updateConfiguration')->once()->with($path, array(
					'scm_repository'   => '',
					'scm_username'     => '',
					'scm_password'     => '',
					'application_name' => '',
				));
		});

		$this->assertTaskOutput('Ignite', 'anahkiasen/rocketeer', $command);
	}
}
