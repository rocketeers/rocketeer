<?php
namespace Rocketeer\Tasks;

use Rocketeer\TestCases\RocketeerTestCase;

class DeployTest extends RocketeerTestCase
{
	public function testCanDeployToServer()
	{
		$this->app['config']->shouldReceive('get')->with('rocketeer::scm')->andReturn(array(
			'repository' => 'https://github.com/'.$this->repository,
			'username'   => '',
			'password'   => '',
		));

		$release = date('YmdHis');
		$matcher = array(
			'git clone --depth 1 -b master "https://github.com/Anahkiasen/html-object.git" ' .$this->server. '/releases/' .$release,
			array(
				"cd $this->server/releases/$release",
				"git submodule update --init --recursive"
			),
			array(
				"cd $this->server/releases/$release",
				exec('which phpunit')." --stop-on-failure "
			),
			array(
				"cd $this->server/releases/$release",
				"chmod -R 755 $this->server/releases/$release/tests",
				"chmod -R g+s $this->server/releases/$release/tests",
				"chown -R www-data:www-data $this->server/releases/$release/tests"
			),
			array(
				"cd $this->server/releases/$release",
				"$this->php artisan migrate --seed"
			),
			"mkdir -p $this->server/shared/tests",
			"mv $this->server/releases/$release/tests/Elements $this->server/shared/tests/Elements",
			"mv $this->server/current $this->server/releases/$release",
			"rm -rf $this->server/current",
			"ln -s $this->server/releases/$release $this->server/current",
		);

		$this->assertTaskHistory('Deploy', $matcher, array(
			'tests'   => true,
			'seed'    => true,
			'migrate' => true
		));
	}
}
