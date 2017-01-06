<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Tasks;

use Rocketeer\TestCases\RocketeerTestCase;

class DeployTestUsingDBRole extends RocketeerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->db_matcher = [
            'git clone "{repository}" "{server}/releases/{release}" --branch="master" --depth="1"',
            [
                'cd {server}/releases/{release}',
                'git submodule update --init --recursive',
            ],
            [
                'cd {server}/releases/{release}',
                '{phpunit} --stop-on-failure',
            ],
            [
                'cd {server}/releases/{release}',
                'chmod -R 755 {server}/releases/{release}/tests',
                'chmod -R g+s {server}/releases/{release}/tests',
                'chown -R www-data:www-data {server}/releases/{release}/tests',
            ],
            [
                'cd {server}/releases/{release}',
                '{php} artisan migrate --force',
            ],
            [
                'cd {server}/releases/{release}',
                '{php} artisan db:seed --force',
            ],
            'mv {server}/current {server}/releases/{release}',
            [
                'ln -s {server}/releases/{release} {server}/current-temp',
                'mv -Tf {server}/current-temp {server}/current',
            ],
        ];

        $this->no_db_matcher = [
            'git clone "{repository}" "{server}/releases/{release}" --branch="master" --depth="1"',
            [
                'cd {server}/releases/{release}',
                'git submodule update --init --recursive',
            ],
            [
                'cd {server}/releases/{release}',
                '{phpunit} --stop-on-failure',
            ],
            [
                'cd {server}/releases/{release}',
                'chmod -R 755 {server}/releases/{release}/tests',
                'chmod -R g+s {server}/releases/{release}/tests',
                'chown -R www-data:www-data {server}/releases/{release}/tests',
            ],
            [
                'cd {server}/releases/{release}',
                '{php} artisan migrate --force',
            ],
            [
                'cd {server}/releases/{release}',
                '{php} artisan db:seed --force',
            ],
            'mv {server}/current {server}/releases/{release}',
            [
                'ln -s {server}/releases/{release} {server}/current-temp',
                'mv -Tf {server}/current-temp {server}/current',
            ],
        ];

        /*
         * Let's use roles
         */

        $this->swapConfig(
            [
                'rocketeer::use_roles' => true,
            ]
        );

        $this->db_role_on = [
            'rocketeer::connections' => [
                'production' => [
                    'servers' => [
                        [
                            'db_role' => true,
                        ],
                    ],
                ],
            ],
        ];

        $this->db_role_off = [
            'rocketeer::connections' => [
                'production' => [
                    'servers' => [
                        [
                            'db_role' => false,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testNoDbRoleNoMigrationsNorSeedsAreRun()
    {
        $this->swapConfig($this->db_role_off);

        $this->app['config']->shouldReceive('get')->with('rocketeer::scm')->andReturn([
            'repository' => 'https://github.com/'.$this->repository,
            'username' => '',
            'password' => '',
        ]);

        $this->assertTaskHistory('Deploy', $this->no_db_matcher, [
            'tests' => true,
            'seed' => true,
            'migrate' => true,
        ]);
    }

    public function testDbRoleMigrationsAndSeedsAreRun()
    {
        $this->swapConfig($this->db_role_on);

        $this->app['config']->shouldReceive('get')->with('rocketeer::scm')->andReturn([
            'repository' => 'https://github.com/'.$this->repository,
            'username' => '',
            'password' => '',
        ]);

        $this->assertTaskHistory('Deploy', $this->db_matcher, [
            'tests' => true,
            'seed' => true,
            'migrate' => true,
        ]);
    }
}
