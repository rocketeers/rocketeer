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

use Rocketeer\Services\Storages\ServerStorage;
use Rocketeer\TestCases\RocketeerTestCase;

class CleanupTest extends RocketeerTestCase
{
    public function testCanCleanupServer()
    {
        $this->mockReleases(function ($mock) {
            return $mock
                ->shouldReceive('getDeprecatedReleases')->once()->andReturn([1, 2])
                ->shouldReceive('getPathToRelease')->times(2)->andReturnUsing(function ($release) {
                    return $release;
                });
        });

        $this->assertTaskOutput('Cleanup', 'Removing <info>2 releases</info> from the server');
    }

    public function testCanPruneAllReleasesIfCleanAll()
    {
        $this->mockReleases(function ($mock) {
            return $mock
                ->shouldReceive('getDeprecatedReleases')->never()
                ->shouldReceive('getNonCurrentReleases')->once()->andReturn([1, 2])
                ->shouldReceive('markReleaseAsValid')->once()
                ->shouldReceive('getPathToRelease')->times(2)->andReturnUsing(function ($release) {
                    return $release;
                });
        });

        ob_start();

        $this->assertTaskOutput('Cleanup', 'Removing <info>2 releases</info> from the server', $this->getCommand([], [
            'clean-all' => true,
            'verbose' => true,
            'pretend' => false,
        ]));

        ob_end_clean();
    }

    public function testCanRemoveAllReleasesAtOnce()
    {
        $this->mockReleases(function ($mock) {
            return $mock
                ->shouldReceive('getDeprecatedReleases')->never()
                ->shouldReceive('getDeprecatedReleases')->once()->andReturn([1, 2])
                ->shouldReceive('getPathToRelease')->times(2)->andReturnUsing(function ($release) {
                    return $release;
                });
        });

        $this->pretendTask('Cleanup')->execute();

        $this->assertHistory([
            'rm -rf {server}/1 {server}/2',
        ]);
    }

    public function testPrintsMessageIfNoCleanup()
    {
        $this->mockReleases(function ($mock) {
            return $mock->shouldReceive('getDeprecatedReleases')->once()->andReturn([]);
        });

        $this->assertTaskOutput('Cleanup', 'No releases to prune from the server');
    }

    public function testAlsoCleansStateFileWhenCleaning()
    {
        $this->mockState([
            0 => true,
            1 => true,
            2 => true,
        ]);

        $this->mockReleases(function ($mock) {
            return $mock
                ->shouldReceive('getDeprecatedReleases')->once()->andReturn([1, 2])
                ->shouldReceive('getPathToRelease')->times(2)->andReturnUsing(function ($release) {
                    return $release;
                });
        });

        $this->task('Cleanup')->execute();

        $storage = new ServerStorage($this->app, 'state');
        $this->assertEquals([true], $storage->get());
    }
}
