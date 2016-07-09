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
use Rocketeer\Services\Releases\ReleasesManager;
use Rocketeer\TestCases\RocketeerTestCase;

class CurrentReleaseTest extends RocketeerTestCase
{
    public function testCanGetCurrentRelease()
    {
        /** @var ReleasesManager $prophecy */
        $prophecy = $this->bindProphecy(ReleasesManager::class);
        $prophecy->getValidationFile()->willReturn([10000000000000 => true]);
        $prophecy->getCurrentRelease()->willReturn('20000000000000');
        $prophecy->getCurrentReleasePath()->shouldBeCalled();

        $this->assertTaskOutput('CurrentRelease', '20000000000000', $this->getCommand([], [
            'pretend' => false,
        ]));
    }

    public function testPrintsMessageIfNoReleaseDeployed()
    {
        /** @var ReleasesManager $prophecy */
        $prophecy = $this->bindProphecy(ReleasesManager::class);
        $prophecy->getValidationFile()->shouldNotBeCalled();
        $prophecy->getCurrentRelease()->willReturn();
        $prophecy->getCurrentReleasePath()->shouldNotBeCalled();

        $this->assertTaskOutput('CurrentRelease', 'No release has yet been deployed');
    }
}
