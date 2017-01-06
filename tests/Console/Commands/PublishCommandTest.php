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

use Rocketeer\TestCases\RocketeerTestCase;

class PublishCommandTest extends RocketeerTestCase
{
    public function testCanFlushLocalStorage()
    {
        unset($this->app['path']);

        $tester = $this->executeCommand('plugin-publish', ['package' => 'foo/bar']);
        $this->assertContains('No configuration found', $tester->getDisplay());
    }
}
