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

use Prophecy\Argument;
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
        $task = $this->pretendTask('Rollback');

        $this->command->getProphecy()->argument('release')->willReturn(15000000000000);
        $task->execute();

        $this->assertHistory([
            [
                'ln -s {server}/releases/15000000000000 {server}/current-temp',
                'mv -Tf {server}/current-temp {server}/current',
            ],
        ]);
    }

    public function testCanGetShownAvailableReleases()
    {
        $task = $this->pretendTask('Rollback');

        $this->command->getProphecy()->option('list')->willReturn(true);
        $this->command->getProphecy()->ask(Argument::cetera())->willReturn(1);
        $task->execute();

        $this->assertHistory([
            [
                'ln -s {server}/releases/15000000000000 {server}/current-temp',
                'mv -Tf {server}/current-temp {server}/current',
            ],
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
        $task = $this->pretendTask('Rollback');
        $this->command->getProphecy()->argument('release')->willReturn('foobar');

        $task->execute();

        $this->assertHistory([]);
    }
}
