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
			'git clone "{repository}" "{server}/releases/{release}" --branch="master" --depth="1"',
			array(
				"cd {server}/releases/{release}",
				"git submodule update --init --recursive"
			),
			array(
				"cd {server}/releases/{release}",
				exec('which phpunit')." --stop-on-failure"
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
		$this->swapConfig(array(
			'rocketeer::scm.shallow'    => false,
			'rocketeer::scm.submodules' => false,
			'rocketeer::scm'            => array(
				'repository' => 'https://github.com/'.$this->repository,
				'username'   => '',
				'password'   => '',
			)
		));

		$matcher = array(
			'git clone "{repository}" "{server}/releases/{release}" --branch="master"',
			array(
				"cd {server}/releases/{release}",
				exec('which phpunit').' --stop-on-failure'
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
			'rocketeer::scm'             => array(
				'repository' => 'https://github.com/'.$this->repository,
				'username'   => '',
				'password'   => '',
			),
			'rocketeer::remote.composer' => function ($composer, $task) {
				return array(
					$composer->selfUpdate(),
					$composer->install([], '--prefer-source'),
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

		$composer = $this->pretendTask('Composer');
		$composer->force = true;
		$composer->fire();

		$this->assertTaskHistory($this->history->getFlattenedHistory(), $matcher, array(
			'tests'   => false,
			'seed'    => false,
			'migrate' => false
		));
	}

	public function testCanUseCopyStrategy()
	{
		$this->swapConfig(array(
			'rocketeer::remote.strategy' => 'copy',
			'rocketeer::scm'             => array(
				'repository' => 'https://github.com/'.$this->repository,
				'username'   => '',
				'password'   => '',
			)
		));

		$this->mockState(array(
			'10000000000000' => true,
		));

		$matcher = array(
			'cp -r {server}/releases/10000000000000 {server}/releases/{release}',
			array(
				'cd {server}/releases/{release}',
				'git reset --hard',
				'git pull',
			),
			array(
				"cd {server}/releases/{release}",
				"chmod -R 755 {server}/releases/{release}/tests",
				"chmod -R g+s {server}/releases/{release}/tests",
				"chown -R www-data:www-data {server}/releases/{release}/tests"
			),
			"mkdir -p {server}/shared/tests",
			"mv {server}/releases/{release}/tests/Elements {server}/shared/tests/Elements",
			"mv {server}/current {server}/releases/{release}",
			"rm -rf {server}/current",
			"ln -s {server}/releases/{release} {server}/current",
		);

		$this->assertTaskHistory('Deploy', $matcher, array(
			'tests'   => false,
			'seed'    => false,
			'migrate' => false
		));
	}

	public function testCanRunDeployWithSeed()
	{
		$matcher = array(
			'git clone "{repository}" "{server}/releases/{release}" --branch="master" --depth="1"',
			array(
				"cd {server}/releases/{release}",
				"git submodule update --init --recursive"
			),
			array(
				"cd {server}/releases/{release}",
				"chmod -R 755 {server}/releases/{release}/tests",
				"chmod -R g+s {server}/releases/{release}/tests",
				"chown -R www-data:www-data {server}/releases/{release}/tests"
			),
			array(
				"cd {server}/releases/{release}",
				"{php} artisan db:seed"
			),
			"mkdir -p {server}/shared/tests",
			"mv {server}/releases/{release}/tests/Elements {server}/shared/tests/Elements",
			"mv {server}/current {server}/releases/{release}",
			"rm -rf {server}/current",
			"ln -s {server}/releases/{release} {server}/current",
		);

		$this->assertTaskHistory('Deploy', $matcher, array(
			'tests'   => false,
			'seed'    => true,
			'migrate' => false,
		));
	}
}
