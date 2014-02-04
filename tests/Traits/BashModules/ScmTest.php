<?php
namespace Rocketeer\Traits\BashModules;

use Rocketeer\TestCases\RocketeerTestCase;

class ScmTest extends RocketeerTestCase
{
	public function testCanForgetCredentialsIfInvalid()
	{
		$this->app['rocketeer.server']->setValue('credentials', array(
			'repository' => 'https://Anahkiasen@bitbucket.org/Anahkiasen/registry.git',
			'username'   => 'Anahkiasen',
			'password'   => 'baz',
		));

		// Create fake remote
		$remote = clone $this->getRemote();
		$remote->shouldReceive('status')->andReturn(1);
		$task = $this->pretendTask();
		$task->remote = $remote;

		$task->cloneRepository($this->server.'/test');
		$this->assertNull($this->app['rocketeer.server']->getValue('credentials'));
	}
}
