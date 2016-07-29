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

use Rocketeer\TestCases\BaseTestCase;

class ConsoleTest extends BaseTestCase
{
    public function testCanRunStandaloneConsole()
    {
        $console = exec(static::$binaries['php'].' bin/rocketeer --version --no-ansi');

        $this->assertContains('Rocketeer', $console);
    }
}
