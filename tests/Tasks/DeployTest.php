<?php
namespace Rocketeer\Tasks;

use Rocketeer\Strategies\Deploy\CopyStrategy;
use Rocketeer\TestCases\RocketeerTestCase;

class DeployTest extends RocketeerTestCase
{
	public function testCanDeployToServer()
	{
		$this->swapConfig(array(
			'rocketeer::scm.repository' => 'https://github.com/'.$this->repository,
			'rocketeer::scm.username'   => '',
			'rocketeer::scm.password'   => '',
		));

		$matcher = array(
			'git clone "{repository}" "{server}/releases/{release}" --branch="master" --depth="1"',
			array(
				"cd {server}/releases/{release}",
				"git submodule update --init --recursive",
			),
			array(
				"cd {server}/releases/{release}",
				"{phpunit} --stop-on-failure",
			),
			array(
				"cd {server}/releases/{release}",
				"chmod -R 755 {server}/releases/{release}/tests",
				"chmod -R g+s {server}/releases/{release}/tests",
				"chown -R www-data:www-data {server}/releases/{release}/tests",
			),
			array(
				"cd {server}/releases/{release}",
				"{php} artisan migrate",
			),
			array(
				"cd {server}/releases/{release}",
				"{php} artisan db:seed",
			),
			"mv {server}/current {server}/releases/{release}",
			array(
				"ln -s {server}/releases/{release} {server}/current-temp",
				"mv -Tf {server}/current-temp {server}/current",
			),
		);

		$this->assertTaskHistory('Deploy', $matcher, array(
			'tests'   => true,
			'seed'    => true,
			'migrate' => true,
		));
	}

	public function testStepsRunnerDoesntCancelWithPermissionsAndShared()
	{
		$this->swapConfig(array(
			'rocketeer::remote.shared'            => [],
			'rocketeer::remote.permissions.files' => [],
		));

		$matcher = array(
			'git clone "{repository}" "{server}/releases/{release}" --branch="master" --depth="1"',
			array(
				"cd {server}/releases/{release}",
				"git submodule update --init --recursive",
			),
			array(
				"cd {server}/releases/{release}",
				"{phpunit} --stop-on-failure",
			),
			array(
				"cd {server}/releases/{release}",
				"{php} artisan migrate",
			),
			array(
				"cd {server}/releases/{release}",
				"{php} artisan db:seed",
			),
			"mv {server}/current {server}/releases/{release}",
			array(
				"ln -s {server}/releases/{release} {server}/current-temp",
				"mv -Tf {server}/current-temp {server}/current",
			),
		);

		$this->assertTaskHistory('Deploy', $matcher, array(
			'tests'   => true,
			'seed'    => true,
			'migrate' => true,
		));
	}

	public function testCanDisableGitOptions()
	{
		$this->swapConfig(array(
			'rocketeer::scm.shallow'    => false,
			'rocketeer::scm.submodules' => false,
			'rocketeer::scm.repository' => 'https://github.com/'.$this->repository,
			'rocketeer::scm.username'   => '',
			'rocketeer::scm.password'   => '',
		));

		$matcher = array(
			'git clone "{repository}" "{server}/releases/{release}" --branch="master"',
			array(
				"cd {server}/releases/{release}",
				'{phpunit} --stop-on-failure',
			),
			array(
				"cd {server}/releases/{release}",
				"chmod -R 755 {server}/releases/{release}/tests",
				"chmod -R g+s {server}/releases/{release}/tests",
				"chown -R www-data:www-data {server}/releases/{release}/tests",
			),
			array(
				"cd {server}/releases/{release}",
				"{php} artisan migrate",
			),
			array(
				"cd {server}/releases/{release}",
				"{php} artisan db:seed",
			),
			"mv {server}/current {server}/releases/{release}",
			array(
				"ln -s {server}/releases/{release} {server}/current-temp",
				"mv -Tf {server}/current-temp {server}/current",
			),
		);

		$this->assertTaskHistory('Deploy', $matcher, array(
			'tests'   => true,
			'seed'    => true,
			'migrate' => true,
		));
	}

	public function testCanUseCopyStrategy()
	{
		$this->swapConfig(array(
			'rocketeer::scm' => array(
				'repository' => 'https://github.com/'.$this->repository,
				'username'   => '',
				'password'   => '',
			),
		));

		$this->app['rocketeer.strategies.deploy'] = new CopyStrategy($this->app);

		$this->mockState(array(
			'10000000000000' => true,
		));

		$matcher = array(
			'cp -a {server}/releases/10000000000000 {server}/releases/{release}',
			array(
				'cd {server}/releases/{release}',
				'git reset --hard',
				'git pull',
			),
			array(
				"cd {server}/releases/{release}",
				"chmod -R 755 {server}/releases/{release}/tests",
				"chmod -R g+s {server}/releases/{release}/tests",
				"chown -R www-data:www-data {server}/releases/{release}/tests",
			),
			"mv {server}/current {server}/releases/{release}",
			array(
				"ln -s {server}/releases/{release} {server}/current-temp",
				"mv -Tf {server}/current-temp {server}/current",
			),
		);

		$this->assertTaskHistory('Deploy', $matcher, array(
			'tests'   => false,
			'seed'    => false,
			'migrate' => false,
		));
	}

	public function testCanRunDeployWithSeed()
	{
		$matcher = array(
			'git clone "{repository}" "{server}/releases/{release}" --branch="master" --depth="1"',
			array(
				"cd {server}/releases/{release}",
				"git submodule update --init --recursive",
			),
			array(
				"cd {server}/releases/{release}",
				"chmod -R 755 {server}/releases/{release}/tests",
				"chmod -R g+s {server}/releases/{release}/tests",
				"chown -R www-data:www-data {server}/releases/{release}/tests",
			),
			array(
				"cd {server}/releases/{release}",
				"{php} artisan db:seed",
			),
			"mv {server}/current {server}/releases/{release}",
			array(
				"ln -s {server}/releases/{release} {server}/current-temp",
				"mv -Tf {server}/current-temp {server}/current",
			),
		);

		$this->assertTaskHistory('Deploy', $matcher, array(
			'tests'   => false,
			'seed'    => true,
			'migrate' => false,
		));
	}

	public function testNoDbRoleNoMigrationsNorSeedsAreRun()
	{
		$this->swapConfig(array(
			'rocketeer::connections' => array(
				'production' => array(
					'servers' => array(
						array(
							'db_role' => false,
						),
					),
				),
			),
		));

		$this->swapConfig(array(
			'rocketeer::scm.repository' => 'https://github.com/'.$this->repository,
			'rocketeer::scm.username'   => '',
			'rocketeer::scm.password'   => '',
		));

		$matcher = array(
			'git clone "{repository}" "{server}/releases/{release}" --branch="master" --depth="1"',
			array(
				"cd {server}/releases/{release}",
				"git submodule update --init --recursive",
			),
			array(
				"cd {server}/releases/{release}",
				"{phpunit} --stop-on-failure",
			),
			array(
				"cd {server}/releases/{release}",
				"chmod -R 755 {server}/releases/{release}/tests",
				"chmod -R g+s {server}/releases/{release}/tests",
				"chown -R www-data:www-data {server}/releases/{release}/tests",
			),
			array(
				"cd {server}/releases/{release}",
				"{php} artisan migrate",
			),
			array(
				"cd {server}/releases/{release}",
				"{php} artisan db:seed",
			),
			"mv {server}/current {server}/releases/{release}",
			array(
				"ln -s {server}/releases/{release} {server}/current-temp",
				"mv -Tf {server}/current-temp {server}/current",
			),
		);

		$this->assertTaskHistory('Deploy', $matcher, array(
			'tests'   => true,
			'seed'    => true,
			'migrate' => true,
		));
	}

	public function testDbRoleMigrationsAndSeedsAreRun()
	{
		$this->swapConfig(array(
			'rocketeer::connections' => array(
				'production' => array(
					'servers' => array(
						array(
							'db_role' => true,
						),
					),
				),
			),
		));

		$this->swapConfig(array(
			'rocketeer::scm.repository' => 'https://github.com/'.$this->repository,
			'rocketeer::scm.username'   => '',
			'rocketeer::scm.password'   => '',
		));

		$matcher = array(
			'git clone "{repository}" "{server}/releases/{release}" --branch="master" --depth="1"',
			array(
				"cd {server}/releases/{release}",
				"git submodule update --init --recursive",
			),
			array(
				"cd {server}/releases/{release}",
				"{phpunit} --stop-on-failure",
			),
			array(
				"cd {server}/releases/{release}",
				"chmod -R 755 {server}/releases/{release}/tests",
				"chmod -R g+s {server}/releases/{release}/tests",
				"chown -R www-data:www-data {server}/releases/{release}/tests",
			),
			array(
				"cd {server}/releases/{release}",
				"{php} artisan migrate",
			),
			array(
				"cd {server}/releases/{release}",
				"{php} artisan db:seed",
			),
			"mv {server}/current {server}/releases/{release}",
			array(
				"ln -s {server}/releases/{release} {server}/current-temp",
				"mv -Tf {server}/current-temp {server}/current",
			),
		);

		$this->assertTaskHistory('Deploy', $matcher, array(
			'tests'   => true,
			'seed'    => true,
			'migrate' => true,
		));
	}
}
