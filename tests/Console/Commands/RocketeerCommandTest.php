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

namespace Rocketeer\Console\Commands;

use Rocketeer\Rocketeer;
use Rocketeer\TestCases\RocketeerTestCase;

class RocketeerCommandTest extends RocketeerTestCase
{
    public function testCanDisplayVersion()
    {
        $tester = $this->executeCommand(null, [
            '--version' => null,
        ]);

        $output = $tester->getDisplay(true);
        $output = trim($output);

        $this->assertEquals('Rocketeer version '.Rocketeer::VERSION, $output);
    }
}
