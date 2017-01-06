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
        $task = $this->pretendTask('Update', [
            'migrate' => true,
            'seed' => true,
        ]);

        $matcher = [
            [
                'cd {server}/releases/20000000000000',
                'git reset --hard',
                'git pull',
            ],
            [
                'cd {server}/releases/20000000000000',
                'chmod -R 755 {server}/releases/20000000000000/tests',
                'chmod -R g+s {server}/releases/20000000000000/tests',
                'chown -R www-data:www-data {server}/releases/20000000000000/tests',
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
                'cd {server}/releases/20000000000000',
                '{php} artisan cache:clear',
            ],
        ];

        $this->assertTaskHistory($task, $matcher);
    }

    public function testCanDisableCacheClearing()
    {
        $task = $this->pretendTask('Update', [
            'migrate' => true,
            'seed' => true,
            'no-clear' => true,
        ]);

        $matcher = [
            [
                'cd {server}/releases/20000000000000',
                'git reset --hard',
                'git pull',
            ],
            [
                'cd {server}/releases/20000000000000',
                'chmod -R 755 {server}/releases/20000000000000/tests',
                'chmod -R g+s {server}/releases/20000000000000/tests',
                'chown -R www-data:www-data {server}/releases/20000000000000/tests',
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

        $this->assertTaskHistory($task, $matcher);
    }
}
