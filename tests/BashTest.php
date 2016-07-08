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

namespace Rocketeer;

use Rocketeer\TestCases\RocketeerTestCase;

class BashTest extends RocketeerTestCase
{
    public function testBashIsCorrectlyComposed()
    {
        $contents = $this->task->runRaw('ls', true, true);

        $this->assertListDirectory($contents);
    }
}
