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

class FlushCommandTest extends RocketeerTestCase
{
    public function testCanFlushLocalStorage()
    {
        $this->localStorage->set('foo', 'bar');

        $this->assertEquals('bar', $this->localStorage->get('foo'));
        $tester = $this->executeCommand('flush');

        $this->assertContains('has been properly', $tester->getDisplay());
        $this->assertNull($this->localStorage->get('foo'));
    }
}
