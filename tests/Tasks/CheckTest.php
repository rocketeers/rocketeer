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

class CheckTest extends RocketeerTestCase
{
    public function testCanCheckScmVersionIfRequired()
    {
        $this->assertTaskHistory('Check', [
            'git --version',
            '{php} -m',
        ]);
    }

    public function testSkipsScmCheckIfNotRequired()
    {
        $this->swapConfig([
            'rocketeer::strategies.deploy' => 'sync',
        ]);

        $this->assertTaskHistory('Check', [
            '{php} -m',
        ]);
    }
}
