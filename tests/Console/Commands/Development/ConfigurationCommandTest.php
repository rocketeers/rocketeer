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

namespace Rocketeer\Console\Commands\Development;

use Rocketeer\TestCases\RocketeerTestCase;

class ConfigurationCommandTest extends RocketeerTestCase
{
    public function testCanDumpConfiguration()
    {
        $tester = $this->executeCommand('debug:config');
        $this->assertContains('application_name', $tester->getDisplay());

        $tester = $this->executeCommand('debug:config', ['key' => 'vcs']);
        $this->assertNotContains('application_name', $tester->getDisplay());
    }
}
