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

		$matcher = array(
			'git clone --depth 1 -b master "https://github.com/Anahkiasen/html-object.git" {server}/releases/{release}',
			array(
				"cd {server}/releases/{release}",
				"git submodule update --init --recursive"
			),
			array(
				"cd {server}/releases/{release}",
				exec('which phpunit')." --stop-on-failure "
			),
			array(
				"cd {server}/releases/{release}",
				"chmod -R 755 {server}/releases/{release}/tests",
				"chmod -R g+s {server}/releases/{release}/tests",
				"chown -R www-data:www-data {server}/releases/{release}/tests"
			),
			array(
				"cd {server}/releases/{release}",
				"{php} artisan migrate --seed"
			),
			"mkdir -p {server}/shared/tests",
			"mv {server}/releases/{release}/tests/Elements {server}/shared/tests/Elements",
			"mv {server}/current {server}/releases/{release}",
			"rm -rf {server}/current",
			"ln -s {server}/releases/{release} {server}/current",
		);

		$this->assertTaskHistory('Deploy', $matcher, array(
			'tests'   => true,
			'seed'    => true,
			'migrate' => true
		));
	}
}
