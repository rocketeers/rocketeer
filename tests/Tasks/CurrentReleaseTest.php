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

class CurrentReleaseTest extends RocketeerTestCase
{
    public function testCanGetCurrentRelease()
    {
        $this->mockReleases(function ($mock) {
            return $mock
                ->shouldReceive('getValidationFile')->once()->andReturn([10000000000000 => true])
                ->shouldReceive('getCurrentRelease')->once()->andReturn('20000000000000')
                ->shouldReceive('getCurrentReleasePath')->once();
        });

        $this->assertTaskOutput('CurrentRelease', '20000000000000');
    }

    public function testPrintsMessageIfNoReleaseDeployed()
    {
        $this->mockReleases(function ($mock) {
            return $mock
                ->shouldReceive('getValidationFile')->never()
                ->shouldReceive('getCurrentRelease')->once()->andReturn(null)
                ->shouldReceive('getCurrentReleasePath')->never();
        });

        $this->assertTaskOutput('CurrentRelease', 'No release has yet been deployed');
    }
}
