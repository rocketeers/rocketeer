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

use Mockery\MockInterface;
use Rocketeer\TestCases\RocketeerTestCase;

class RollbackTest extends RocketeerTestCase
{
    public function testCanRollbackRelease()
    {
        $this->mockOperatingSystem();

        $this->assertTaskHistory('Rollback', [
            'rm -rf {server}/current',
            'ln -s {server}/releases/10000000000000 {server}/current'
        ]);
    }

    public function testCanRollbackToSpecificRelease()
    {
        $this->mockOperatingSystem();
        $task = $this->pretendTask('Rollback');

        $this->command->shouldReceive('argument')->with('release')->andReturn(15000000000000);
        $task->execute();

        $this->assertHistory([
            'rm -rf {server}/current',
            'ln -s {server}/releases/15000000000000 {server}/current'
        ]);
    }

    public function testCanGetShownAvailableReleases()
    {
        $this->mockOperatingSystem();
        $task = $this->pretendTask('Rollback');

        $this->command->shouldReceive('option')->with('list')->andReturn(true);
        $this->command->shouldReceive('askWith')->andReturn(1);
        $task->execute();

        $this->assertHistory([
            'rm -rf {server}/current',
            'ln -s {server}/releases/15000000000000 {server}/current'
        ]);
    }

    public function testCantRollbackIfNoPreviousRelease()
    {
        $this->mockReleases(function (MockInterface $mock) {
            return $mock->shouldReceive('getPreviousRelease')->andReturn(null);
        });

        $status = $this->pretendTask('Rollback')->execute();
        $this->assertContains('Rocketeer could not rollback as no releases have yet been deployed', $status);
    }

    public function testCantRollbackToUnexistingRelease()
    {
        $task = $this->pretendTask('Rollback');
        $this->command->shouldReceive('argument')->with('release')->andReturn('foobar');

        $task->execute();

        $this->assertHistory([]);
    }
}
