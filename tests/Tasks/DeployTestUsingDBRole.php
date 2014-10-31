<?php

namespace Rocketeer\Tasks;

use Rocketeer\Strategies\Deploy\CopyStrategy;
use Rocketeer\TestCases\RocketeerTestCase;

class DeployTestUsingDBRole extends RocketeerTestCase
{

    public function setUp()
    {
        parent::setUp();


        $this->db_matcher = array(
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

        $this->no_db_matcher = array(
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

        /*
         * Let's use roles
         */

        $this->swapConfig(
                array(
                    'rocketeer::use_roles' => true,
                )
        );

        $this->db_role_on = array(
            'rocketeer::connections' => array(
                'production' => array(
                    'servers' => array(
                        array(
                            'db_role' => true,
                        ),
                    ),
                ),
            ),
        );

        $this->db_role_off = array(
            'rocketeer::connections' => array(
                'production' => array(
                    'servers' => array(
                        array(
                            'db_role' => false,
                        ),
                    ),
                ),
            ),
        );
    }

    public function testNoDbRoleNoMigrationsNorSeedsAreRun()
    {

        $this->swapConfig($this->db_role_off);

        $this->app['config']->shouldReceive('get')->with('rocketeer::scm')->andReturn(array(
            'repository' => 'https://github.com/' . $this->repository,
            'username' => '',
            'password' => '',
        ));

        $this->assertTaskHistory('Deploy', $this->no_db_matcher, array(
            'tests' => true,
            'seed' => true,
            'migrate' => true,
        ));
    }

    public function testDbRoleMigrationsAndSeedsAreRun()
    {
        $this->swapConfig($this->db_role_on);

        $this->app['config']->shouldReceive('get')->with('rocketeer::scm')->andReturn(array(
            'repository' => 'https://github.com/' . $this->repository,
            'username' => '',
            'password' => '',
        ));

        $this->assertTaskHistory('Deploy', $this->db_matcher, array(
            'tests' => true,
            'seed' => true,
            'migrate' => true,
        ));
    }

}
