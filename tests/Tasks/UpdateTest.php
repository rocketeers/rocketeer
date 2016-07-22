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

class UpdateTest extends RocketeerTestCase
{
    public function testCanUpdateRepository()
    {
        $this->usesLaravel();
        $task = $this->pretendTask('Update', [
            '--migrate' => true,
            '--seed' => true,
        ]);

        $matcher = [
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
            [
                'cd {server}/releases/{release}',
                '{php} artisan migrate --force',
            ],
            [
                'cd {server}/releases/{release}',
                '{php} artisan db:seed --force',
            ],
            [
                'cd {server}/releases/{release}',
                '{php} artisan cache:clear',
            ],
        ];

        $this->assertTaskHistory($task, $matcher);
    }

    public function testCanDisableCacheClearing()
    {
        $this->usesLaravel();

        $matcher = [
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
            [
                'cd {server}/releases/{release}',
                '{php} artisan migrate --force',
            ],
            [
                'cd {server}/releases/{release}',
                '{php} artisan db:seed --force',
            ],
        ];

        $this->assertTaskHistory('Update', $matcher, [
            '--migrate' => true,
            '--seed' => true,
            '--no-clear' => true,
        ]);
    }
}
