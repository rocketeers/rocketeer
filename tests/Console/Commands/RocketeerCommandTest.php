<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Console\Commands;

use Rocketeer\TestCases\RocketeerTestCase;

class RocketeerCommandTest extends RocketeerTestCase
{
    public function testCanDisplayVersion()
    {
        $tester = $this->executeCommand(null, [
            '--version' => null,
        ]);

        $output = $tester->getDisplay();
        $output = trim($output);
        $version = strip_tags($this->console->getLongVersion());

        $this->assertEquals($version, $output);
    }
}
