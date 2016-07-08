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

namespace Rocketeer\Console;

use Rocketeer\TestCases\RocketeerTestCase;

class ConsoleTest extends RocketeerTestCase
{
    public function testCanRunStandaloneConsole()
    {
        $console = exec($this->binaries['php'].' bin/rocketeer --version --no-ansi');
        $version = strip_tags($this->console->getLongVersion());

        $this->assertContains($version, $console);
    }
}
