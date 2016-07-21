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

use Rocketeer\Services\Releases\ReleasesManager;
use Rocketeer\TestCases\RocketeerTestCase;

class RollbackTest extends RocketeerTestCase
{
    public function testCanRollbackRelease()
    {
        $this->assertTaskHistory('Rollback', [
            [
                'ln -s {server}/releases/10000000000000 {server}/current-temp',
                'mv -Tf {server}/current-temp {server}/current',
            ],
        ]);
    }

    public function testCanRollbackToSpecificRelease()
    {
        $this->assertTaskHistory('Rollback', [
            [
                'ln -s {server}/releases/15000000000000 {server}/current-temp',
                'mv -Tf {server}/current-temp {server}/current',
            ],
        ], [
            'release' => 15000000000000,
        ]);
    }

    public function testCanGetShownAvailableReleases()
    {
        $this->assertTaskHistory('Rollback', [
            [
                'ln -s {server}/releases/20000000000000 {server}/current-temp',
                'mv -Tf {server}/current-temp {server}/current',
            ],
        ], [
            '--list' => true,
        ]);
    }

    public function testCantRollbackIfNoPreviousRelease()
    {
        $this->bindProphecy(ReleasesManager::class);

        $status = $this->pretendTask('Rollback')->execute();
        $this->assertContains('Rocketeer could not rollback as no releases have yet been deployed', $status);
    }

    public function testCantRollbackToUnexistingRelease()
    {
        $this->assertTaskHistory('Rollback', [], [
            'release' => 'foobar',
        ]);
    }
}
