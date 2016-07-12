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

namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\TestCases\RocketeerTestCase;

class CreateReleaseTest extends RocketeerTestCase
{
    public function testAddsDeployedReleaseToList()
    {
        $this->pretend();
        $this->task('CreateRelease')->execute();

        $this->assertCount(4, $this->releasesManager->getReleases());
    }
}
