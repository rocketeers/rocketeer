<?php

class DeployTest extends RocketeerTests
{
	public function testCanDeployToServer()
	{
		$this->app['config']->shouldReceive('get')->with('rocketeer::scm')->andReturn(array(
			'repository' => 'https://github.com/'.$this->repository,
			'username'   => '',
			'password'   => '',
		));

		$task = $this->pretendDeployTask();
		$task->execute();

		$server = $this->server;
		$release = $this->app['rocketeer.releases']->getCurrentRelease();
		$matcher = array(
			"git clone --depth 1 -b master https://github.com/Anahkiasen/html-object.git $server/releases/$release",
			array(
				"cd $server/releases/$release",
				"git submodule update --init --recursive"
			),
			array(
				"cd $server/releases/$release",
				"/Users/anahkiasen/.composer/vendor/bin/phpunit --stop-on-failure "
			),
			array(
				"cd $server/releases/$release",
				"chmod -R 755 $server/releases/$release/tests",
				"chmod -R g+s $server/releases/$release/tests",
				"chown -R www-data:www-data $server/releases/$release/tests"
			),
			array(
				"cd $server/releases/$release",
				"/usr/local/bin/php artisan migrate --seed"
			),
			"mkdir -p $server/shared/tests",
			"mv $server/releases/$release/tests/Elements $server/shared/tests/Elements",
			"mv $server/current $server/releases/$release",
			"rm -rf $server/current",
			"ln -s $server/releases/$release $server/current",
		);

		$this->assertEquals($matcher, $task->getHistory());
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get a pretend Deploy task
	 *
	 * @param array $options
	 *
	 * @return Task
	 */
	protected function pretendDeployTask($options = array())
	{
		$options = array_merge(array(
			'tests'   => true,
			'seed'    => true,
			'migrate' => true
		), $options);

		return $this->pretendTask('Deploy', $options);
	}
}
