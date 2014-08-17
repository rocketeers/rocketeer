<?php
namespace Rocketeer\Traits\BashModules;

use Rocketeer\TestCases\RocketeerTestCase;

class ScmTest extends RocketeerTestCase
{
	public function testCanForgetCredentialsIfInvalid()
	{
		$this->app['rocketeer.storage.local']->set('credentials', array(
			'repository' => 'https://bitbucket.org/Anahkiasen/registry.git',
			'username'   => 'Anahkiasen',
			'password'   => 'baz',
		));

		$this->mock('rocketeer.bash', 'Bash', function ($mock) {
			return $mock
				->shouldIgnoreMissing()
				->shouldReceive('checkStatus')->andReturn(false);
		});

		$task = $this->pretendTask();

		$task->getStrategy('Deploy')->deploy($this->server.'/test');
		$this->assertNull($this->app['rocketeer.storage.local']->get('credentials'));
	}
}
