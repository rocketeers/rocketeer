<?php
namespace Rocketeer\Tests\Tasks;

use Rocketeer\Tests\TestCases\RocketeerTestCase;

class IgniteTest extends RocketeerTestCase
{
	public function testCanIgniteConfigurationOutsideLaravel()
	{
		$command = $this->getCommand(array('ask' => 'foobar'));

		$this->mock('rocketeer.igniter', 'Igniter', function ($mock) {
			return $mock->shouldReceive('exportConfiguration')->once()->with(array(
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

		$this->mock('rocketeer.igniter', 'Igniter', function ($mock) {
			return $mock->shouldReceive('exportConfiguration')->never();
		});

		$this->assertTaskOutput('Ignite', 'foobar', $command);
	}
}
