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

class RollbackTest extends RocketeerTestCase
{
    public function testCanRollbackRelease()
    {
        $this->task('Rollback')->execute();

        $this->assertEquals(10000000000000, $this->releasesManager->getCurrentRelease());
    }

    public function testCanRollbackToSpecificRelease()
    {
        $this->mockCommand([], ['argument' => 15000000000000]);
        $this->command->shouldReceive('option')->andReturn([]);

        $this->task('Rollback')->execute();

        $this->assertEquals(15000000000000, $this->releasesManager->getCurrentRelease());
    }

    public function testCanGetShownAvailableReleases()
    {
        $this->command = $this->mockCommand(['list' => true]);
        $this->command->shouldReceive('askWith')->andReturn(1);

        $this->task('Rollback')->execute();

        $this->assertEquals(15000000000000, $this->releasesManager->getCurrentRelease());
    }

    public function testCantRollbackIfNoPreviousRelease()
    {
        $this->mockReleases(function ($mock) {
            return $mock->shouldReceive('getPreviousRelease')->andReturn(null);
        });

        $status = $this->task('Rollback')->execute();
        $this->assertContains('Rocketeer could not rollback as no releases have yet been deployed', $status);
    }

    public function testCantRollbackToUnexistingRelease()
    {
        $this->mockCommand([], ['argument' => 'foobar']);
        $this->command->shouldReceive('option')->andReturn([]);

        $this->task('Rollback')->execute();

        $this->assertEquals(20000000000000, $this->releasesManager->getCurrentRelease());
    }
}
