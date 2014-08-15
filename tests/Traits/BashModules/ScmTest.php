<?php
namespace Rocketeer\Traits\BashModules;

use Rocketeer\TestCases\RocketeerTestCase;

class ScmTest extends RocketeerTestCase
{
	public function testCanForgetCredentialsIfInvalid()
	{
		$this->app['rocketeer.storage.local']->set('credentials', array(
			'repository' => 'https://Anahkiasen@bitbucket.org/Anahkiasen/registry.git',
			'username'   => 'Anahkiasen',
			'password'   => 'baz',
		));

		// Create fake remote
		$remote = $this->getRemote();
		$remote->shouldReceive('status')->andReturn(1);
		$this->app['rocketeer.remote'] = $remote;

		$task = $this->pretendTask();

		$task->getStrategy('Deploy')->deploy($this->server.'/test');
		$this->assertNull($this->app['rocketeer.storage.local']->get('credentials'));
	}
}
