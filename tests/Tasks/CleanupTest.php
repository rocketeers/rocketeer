<?php
namespace Rocketeer\Tasks;

use Mockery\MockInterface;
use Rocketeer\Services\Storages\ServerStorage;
use Rocketeer\TestCases\RocketeerTestCase;

class CleanupTest extends RocketeerTestCase
{
    public function testCanCleanupServer()
    {
        $this->mockReleases(function (MockInterface $mock) {
            return $mock
                ->shouldReceive('getDeprecatedReleases')->once()->andReturn(array(1, 2))
                ->shouldReceive('getPathToRelease')->times(2)->andReturnUsing(function ($release) {
                    return $release;
                });
        });

        $this->assertTaskOutput('Cleanup', 'Removing <info>2 releases</info> from the server');
    }

    public function testCanPruneAllReleasesIfCleanAll()
    {
        $this->mockReleases(function (MockInterface $mock) {
            return $mock
                ->shouldReceive('getDeprecatedReleases')->never()
                ->shouldReceive('getNonCurrentReleases')->once()->andReturn(array(1, 2))
                ->shouldReceive('markReleaseAsValid')->once()
                ->shouldReceive('getPathToRelease')->times(2)->andReturnUsing(function ($release) {
                    return $release;
                });
        });

        ob_start();

        $this->assertTaskOutput('Cleanup', 'Removing <info>2 releases</info> from the server', $this->getCommand(array(), array(
            'clean-all' => true,
            'verbose'   => true,
            'pretend'   => false,
        )));

        ob_end_clean();
    }

    public function testCanRemoveAllReleasesAtOnce()
    {
        $this->mockReleases(function (MockInterface $mock) {
            return $mock
                ->shouldReceive('getDeprecatedReleases')->never()
                ->shouldReceive('getDeprecatedReleases')->once()->andReturn(array(1, 2))
                ->shouldReceive('getPathToRelease')->times(2)->andReturnUsing(function ($release) {
                    return $release;
                });
        });

        $this->pretendTask('Cleanup')->execute();

        $this->assertHistory(array(
            'rm -rf {server}/1 {server}/2',
        ));
    }

    public function testPrintsMessageIfNoCleanup()
    {
        $this->mockReleases(function (MockInterface $mock) {
            return $mock->shouldReceive('getDeprecatedReleases')->once()->andReturn(array());
        });

        $this->assertTaskOutput('Cleanup', 'No releases to prune from the server');
    }

    public function testAlsoCleansStateFileWhenCleaning()
    {
        $this->mockState(array(
            0 => true,
            1 => true,
            2 => true,
        ));

        $this->mockReleases(function (MockInterface $mock) {
            return $mock
                ->shouldReceive('getDeprecatedReleases')->once()->andReturn(array(1, 2))
                ->shouldReceive('getPathToRelease')->times(2)->andReturnUsing(function ($release) {
                    return $release;
                });
        });

        $this->pretendTask('Cleanup')->execute();

        $storage = new ServerStorage($this->app, 'state');
        $this->assertEquals([true], $storage->get());
    }
}
