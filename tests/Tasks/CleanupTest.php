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
use Rocketeer\Services\Storages\ServerStorage;
use Rocketeer\TestCases\RocketeerTestCase;

class CleanupTest extends RocketeerTestCase
{
    public function testCanCleanupServer()
    {
        /** @var ReleasesManager $releases */
        $releases = $this->bindProphecy(ReleasesManager::class);
        $releases->getDeprecatedReleases()->willReturn([1, 2]);
        $releases->getPathToRelease(Argument::any())->shouldBeCalledTimes(2)->willReturnArgument(0);

        $this->assertTaskOutput('Cleanup', 'Removing <info>2 releases</info> from the server');
    }

    public function testCanPruneAllReleasesIfCleanAll()
    {
        /** @var ReleasesManager $releases */
        $releases = $this->bindProphecy(ReleasesManager::class);
        $releases->getDeprecatedReleases()->shouldNotBeCalled();
        $releases->getNonCurrentReleases()->willReturn([1, 2]);
        $releases->markReleaseAsValid()->shouldBeCalled();
        $releases->getPathToRelease(Argument::any())->shouldBeCalledTimes(2)->willReturnArgument(1);

        ob_start();

        $this->assertTaskOutput('Cleanup', 'Removing <info>2 releases</info> from the server', [
            '--clean-all' => true,
            '--verbose' => true,
            '--pretend' => true,
        ]);

        ob_end_clean();
    }

    public function testCanRemoveAllReleasesAtOnce()
    {
        /** @var ReleasesManager $prophecy */
        $prophecy = $this->bindProphecy(ReleasesManager::class);
        $prophecy->getDeprecatedReleases()->shouldBeCalled()->willReturn([1, 2]);
        $prophecy->getPathToRelease(Argument::any())->shouldBeCalledTimes(2)->willReturnArgument(0);

        $this->pretendTask('Cleanup')->execute();

        $this->assertHistory([
            'rm -rf {server}/1 {server}/2',
        ]);
    }

    public function testPrintsMessageIfNoCleanup()
    {
        /** @var ReleasesManager $prophecy */
        $prophecy = $this->bindProphecy(ReleasesManager::class);
        $prophecy->getDeprecatedReleases()->shouldBeCalled()->willReturn([]);

        $this->assertTaskOutput('Cleanup', 'No releases to prune from the server');
    }

    public function testAlsoCleansStateFileWhenCleaning()
    {
        $this->mockState([
            0 => true,
            1 => true,
            2 => true,
        ]);

        /** @var ReleasesManager $prophecy */
        $prophecy = $this->bindProphecy(ReleasesManager::class);
        $prophecy->getDeprecatedReleases()->shouldBeCalled()->willReturn([1, 2]);
        $prophecy->getPathToRelease(Argument::any())->shouldBeCalledTimes(2)->willReturnArgument(0);

        $this->task('Cleanup')->execute();

        $storage = new ServerStorage($this->container);
        $this->assertEquals([true], $storage->get());
    }
}
