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

	public function testCanDisableGitOptions()
	{
		$this->app['config'] = $this->getConfig(array(
			'rocketeer::scm.shallow'    => false,
			'rocketeer::scm.submodules' => false,
			'rocketeer::scm' => array(
				'repository' => 'https://github.com/'.$this->repository,
				'username'   => '',
				'password'   => '',
			)
		));

		$matcher = array(
			'git clone -b master "https://github.com/Anahkiasen/html-object.git" {server}/releases/{release}',
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

	public function testCanConfigureComposerCommands()
	{
		$this->swapConfig(array(
			'rocketeer::scm' => array(
				'repository' => 'https://github.com/'.$this->repository,
				'username'   => '',
				'password'   => '',
			),
			'rocketeer::remote.composer' => function($task) {
				return array(
					$task->composer('self-update'),
					$task->composer('install --prefer-source'),
				);
			},
		));

		$matcher = array(
			array(
				"cd {server}/releases/{release}",
			  "{composer} self-update",
			  "{composer} install --prefer-source",
			),
		);

		$deploy = $this->pretendTask('Deploy');
		$deploy->runComposer(true);

		$this->assertTaskHistory($deploy->getHistory(), $matcher, array(
			'tests'   => false,
			'seed'    => false,
			'migrate' => false
		));
	}
}
