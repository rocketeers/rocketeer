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

use Rocketeer\Strategies\CreateRelease\CopyStrategy;
use Rocketeer\TestCases\RocketeerTestCase;

class DeployTest extends RocketeerTestCase
{
    public function testCanDeployToServer()
    {
        $matcher = [
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
            'mv {server}/current {server}/releases/{release}',
            [
                'ln -s {server}/releases/{release} {server}/current-temp',
                'mv -Tf {server}/current-temp {server}/current',
            ],
        ];

        $this->assertTaskHistory('Deploy', $matcher, [
            '--tests' => true,
            '--seed' => true,
            '--migrate' => true,
        ]);
    }

    public function testStepsRunnerDoesntCancelWithPermissionsAndShared()
    {
        $this->swapConfig([
            'remote.shared' => [],
            'remote.permissions.files' => [],
        ]);

        $matcher = [
            'git clone "{repository}" "{server}/releases/{release}" --branch="master" --depth="1"',
            [
                'cd {server}/releases/{release}',
                'git submodule update --init --recursive',
            ],
            [
                'cd {server}/releases/{release}',
                '{phpunit} --stop-on-failure',
            ],
            'mv {server}/current {server}/releases/{release}',
            [
                'ln -s {server}/releases/{release} {server}/current-temp',
                'mv -Tf {server}/current-temp {server}/current',
            ],
        ];

        $this->assertTaskHistory('Deploy', $matcher, [
            '--tests' => true,
            '--seed' => true,
            '--migrate' => true,
        ]);
    }

    public function testCanDisableGitOptions()
    {
        $this->swapScmConfiguration([
            'shallow' => false,
            'submodules' => false,
        ]);

        $matcher = [
            'git clone "{repository}" "{server}/releases/{release}" --branch="master"',
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
            'mv {server}/current {server}/releases/{release}',
            [
                'ln -s {server}/releases/{release} {server}/current-temp',
                'mv -Tf {server}/current-temp {server}/current',
            ],
        ];

        $this->assertTaskHistory('Deploy', $matcher, [
            '--tests' => true,
            '--seed' => true,
            '--migrate' => true,
        ]);
    }

    public function testCanUseCopyStrategy()
    {
        $this->container->add('rocketeer.strategies.create-release', new CopyStrategy($this->container));
        $this->mockState([
            '10000000000000' => true,
        ]);

        $matcher = [
            'cp -a {server}/releases/10000000000000 {server}/releases/{release}',
            [
                'cd {server}/releases/{release}',
                'git reset --hard',
                'git pull --recurse-submodules',
            ],
            [
                'cd {server}/releases/{release}',
                'chmod -R 755 {server}/releases/{release}/tests',
                'chmod -R g+s {server}/releases/{release}/tests',
                'chown -R www-data:www-data {server}/releases/{release}/tests',
            ],
            'mv {server}/current {server}/releases/{release}',
            [
                'ln -s {server}/releases/{release} {server}/current-temp',
                'mv -Tf {server}/current-temp {server}/current',
            ],
        ];

        $this->assertTaskHistory('Deploy', $matcher, [
            '--tests' => false,
            '--seed' => false,
            '--migrate' => false,
        ]);
    }

    public function testCanRunDeployWithSeed()
    {
        $this->usesLaravel();

        $matcher = [
            'git clone "{repository}" "{server}/releases/{release}" --branch="master" --depth="1"',
            [
                'cd {server}/releases/{release}',
                'git submodule update --init --recursive',
            ],
            [
                'cd {server}/releases/{release}',
                'chmod -R 755 {server}/releases/{release}/tests',
                'chmod -R g+s {server}/releases/{release}/tests',
                'chown -R www-data:www-data {server}/releases/{release}/tests',
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

        $this->assertTaskHistory('Deploy', $matcher, [
            '--tests' => false,
            '--seed' => true,
            '--migrate' => false,
        ]);
    }
}
