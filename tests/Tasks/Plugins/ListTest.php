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

namespace Rocketeer\Tasks\Plugins;

use Rocketeer\Dummies\DummyNotifier;
use Rocketeer\TestCases\RocketeerTestCase;

class ListTest extends RocketeerTestCase
{
    public function testCanListRegisterdPlugins()
    {
        $tester = $this->executeCommand('plugin:list');

        $this->assertContains(DummyNotifier::class, $tester->getDisplay());
    }
}
