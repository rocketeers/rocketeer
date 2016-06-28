<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services;

use Mockery\MockInterface;
use Rocketeer\Bash;
use Rocketeer\TestCases\RocketeerTestCase;

class ReleasesManagerTest extends RocketeerTestCase
{
    public function testCanGetCurrentRelease()
    {
        $currentRelease = $this->releasesManager->getCurrentRelease();

        $this->assertEquals(20000000000000, $currentRelease);
    }

    public function testCanGetStateOfReleases()
    {
        $validation = $this->releasesManager->getValidationFile();

        $this->assertEquals([
            10000000000000 => true,
            15000000000000 => false,
            20000000000000 => true,
        ], $validation);
    }

    public function testCanGetInvalidReleases()
    {
        $validation = $this->releasesManager->getInvalidReleases();

        $this->assertEquals([1 => 15000000000000], $validation);
    }

    public function testCanGetValidReleases()
    {
        $validation = $this->releasesManager->getValidReleases();

        $this->assertEquals([0 => 20000000000000, 1 => 10000000000000], $validation);
    }

    public function testCanUpdateStateOfReleases()
    {
        $this->releasesManager->markReleaseAsValid(15000000000000);
        $validation = $this->releasesManager->getValidationFile();

        $this->assertEquals([
            10000000000000 => true,
            15000000000000 => true,
            20000000000000 => true,
        ], $validation);
    }

    public function testCanMarkRelease()
    {
        $this->releasesManager->markRelease(123456789, false);
        $validation = $this->releasesManager->getValidationFile();

        $this->assertEquals([
            10000000000000 => true,
            15000000000000 => false,
            20000000000000 => true,
            123456789 => false,
        ], $validation);
    }

    public function testCanMarkReleaseAsValid()
    {
        $this->releasesManager->markReleaseAsValid(123456789);
        $validation = $this->releasesManager->getValidationFile();

        $this->assertEquals([
            10000000000000 => true,
            15000000000000 => false,
            20000000000000 => true,
            123456789 => true,
        ], $validation);
    }

    public function testCanGetCurrentReleaseFromServerIfUncached()
    {
        $this->mock('storage.local', 'Storage', function (MockInterface $mock) {
            return $mock
                ->shouldReceive('getSeparator')->andReturn('/')
                ->shouldReceive('getLineEndings')->andReturn(PHP_EOL);
        });

        $currentRelease = $this->releasesManager->getCurrentRelease();

        $this->assertEquals(20000000000000, $currentRelease);
    }

    public function testCanGetReleasesPath()
    {
        $releasePath = $this->releasesManager->getReleasesPath();

        $this->assertEquals($this->server.'/releases', $releasePath);
    }

    public function testCanGetCurrentReleaseFolder()
    {
        $currentReleasePath = $this->releasesManager->getCurrentReleasePath();

        $this->assertEquals($this->server.'/releases/20000000000000', $currentReleasePath);
    }

    public function testCanGetReleases()
    {
        $releases = $this->releasesManager->getReleases();

        $this->assertEquals([1 => 15000000000000, 0 => 20000000000000, 2 => 10000000000000], $releases);
    }

    public function testCanGetDeprecatedReleases()
    {
        $releases = [
            '10000000000000' => false,
            '15000000000000' => false,
            '20000000000000' => true,
            '25000000000000' => true,
            '30000000000000' => false,
            '35000000000000' => false,
            '40000000000000' => false,
            '45000000000000' => true,
            '50000000000000' => true,
        ];

        foreach ($releases as $release => $state) {
            @mkdir($this->server.'/releases/'.$release);
        }

        $this->mockState($releases);

        $releases = $this->releasesManager->getDeprecatedReleases(5);

        $this->assertEquals([
            40000000000000,
            35000000000000,
            30000000000000,
            15000000000000,
            10000000000000,
        ], $releases);
    }

    public function testCanGetPreviousValidRelease()
    {
        $currentRelease = $this->releasesManager->getPreviousRelease();

        $this->assertEquals(10000000000000, $currentRelease);
    }

    public function testReturnsCurrentReleaseIfNoPreviousValidRelease()
    {
        $this->mockState([
            '10000000000000' => false,
            '15000000000000' => false,
            '20000000000000' => true,
        ]);

        $currentRelease = $this->releasesManager->getPreviousRelease();

        $this->assertEquals(20000000000000, $currentRelease);
    }

    public function testReturnsCurrentReleaseIfOnlyRelease()
    {
        $this->mockState([
            '20000000000000' => true,
        ]);

        $currentRelease = $this->releasesManager->getPreviousRelease();

        $this->assertEquals(20000000000000, $currentRelease);
    }

    public function testReturnsCorrectPreviousReleaseIfUpdatedBeforehand()
    {
        $this->mockState([
            '20000000000000' => true,
        ]);

        $previous = $this->releasesManager->getPreviousRelease();

        $this->assertEquals(20000000000000, $previous);
    }

    public function testCanReturnPreviousReleaseIfNoReleases()
    {
        $this->mock('rocketeer.bash', Bash::class, function (MockInterface $mock) {
            return $mock
                ->shouldReceive('listContents')->once()->with($this->server.'/releases')->andReturn([]);
        });

        $this->mockState([]);

        $previous = $this->releasesManager->getPreviousRelease();
        $this->assertNull($previous);
    }

    public function testCanGetFolderInRelease()
    {
        $folder = $this->releasesManager->getCurrentReleasePath('{path.storage}');

        $this->assertEquals($this->server.'/releases/20000000000000/app/storage', $folder);
    }

    public function testDoesntPingForReleasesAllTheFuckingTime()
    {
        $this->mock('rocketeer.bash', Bash::class, function (MockInterface $mock) {
            return $mock
                ->shouldReceive('listContents')->once()->with($this->server.'/releases')->andReturn([20000000000000]);
        });

        $this->releasesManager->getNonCurrentReleases();
        $this->releasesManager->getNonCurrentReleases();
        $this->releasesManager->getNonCurrentReleases();
        $this->releasesManager->getNonCurrentReleases();
    }

    public function testDoesntPingForReleasesIfNoReleases()
    {
        $this->mock('rocketeer.bash', Bash::class, function (MockInterface $mock) {
            return $mock
                ->shouldReceive('listContents')->once()->with($this->server.'/releases')->andReturn([]);
        });

        $this->releasesManager->getNonCurrentReleases();
        $this->releasesManager->getNonCurrentReleases();
        $this->releasesManager->getNonCurrentReleases();
        $this->releasesManager->getNonCurrentReleases();
    }

    public function testIgnoresErrorsAndStuffWhenFetchingReleases()
    {
        $this->mock('rocketeer.bash', Bash::class, function (MockInterface $mock) {
            return $mock
                ->shouldReceive('listContents')->times(1)->with($this->server.'/releases')->andReturn(['IMPOSSIBLE BECAUSE NOPE FUCK YOU']);
        });

        $releases = $this->releasesManager->getReleases();

        $this->assertEmpty($releases);
    }

    public function testResetsReleasesCacheWhenSwitchingServer()
    {
        $this->mock('rocketeer.bash', Bash::class, function (MockInterface $mock) {
            return $mock
                ->shouldReceive('listContents')->twice()->with($this->server.'/releases')->andReturn([20000000000000]);
        });

        $releases = $this->releasesManager->getReleases();
        $this->assertEquals([20000000000000], $releases);

        $this->connections->setConnection('staging');
        $releases = $this->releasesManager->getReleases();
        $this->assertEquals([20000000000000], $releases);
    }

    public function testCanManuallySetNameOfNextRelease()
    {
        $custom = '20110101010101';
        $this->mockCommand(['release' => $custom]);

        $release = $this->releasesManager->getNextRelease();
        $this->assertEquals($custom, $release);
    }

    public function testDoesntAllowInvalidCustomReleases()
    {
        $custom = 'foobar';
        $this->mockCommand(['release' => $custom]);

        $release = $this->releasesManager->getNextRelease();
        $this->assertNotEquals($custom, $release);
    }

    public function testReleasesArentCastToInteger()
    {
        $releases = $this->releasesManager->getReleases();

        $this->assertInternalType('string', $releases[0]);
    }
}
