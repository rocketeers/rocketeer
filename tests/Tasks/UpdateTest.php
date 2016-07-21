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
        $this->usesLaravel(true);
        $task = $this->pretendTask('Update', [
            'migrate' => true,
            'seed' => true,
        ]);

        $matcher = [
            [
                'cd {server}/releases/20000000000000',
                'git reset --hard',
                'git pull --recurse-submodules',
            ],
            [
                'cd {server}/releases/20000000000000',
                'chmod -R 755 {server}/releases/20000000000000/tests',
                'chmod -R g+s {server}/releases/20000000000000/tests',
                'chown -R www-data:www-data {server}/releases/20000000000000/tests',
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
        $this->usesLaravel(true);

        $matcher = [
            [
                'cd {server}/releases/20000000000000',
                'git reset --hard',
                'git pull --recurse-submodules',
            ],
            [
                'cd {server}/releases/20000000000000',
                'chmod -R 755 {server}/releases/20000000000000/tests',
                'chmod -R g+s {server}/releases/20000000000000/tests',
                'chown -R www-data:www-data {server}/releases/20000000000000/tests',
            ],
        ];

        $this->assertTaskHistory('Update', $matcher, [
            'migrate' => true,
            'seed' => true,
            'no-clear' => true,
        ]);
    }
}
