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

class MigrateTest extends RocketeerTestCase
{
    public function testCanRunMigrations()
    {
        $this->usesLaravel(true);
        $this->config->set('strategies.migrate', 'Laravel');

        $this->assertTaskHistory(Migrate::class, [
            [
                'cd {server}/releases/{release}',
                '{php} artisan migrate --force',
            ],
            [
                'cd {server}/releases/{release}',
                '{php} artisan db:seed --force',
            ],
        ], [
            'migrate' => true,
            'seed' => true,
        ]);
    }
}
