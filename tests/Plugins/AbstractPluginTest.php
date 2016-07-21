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

namespace Rocketeer\Plugins;

use Rocketeer\Dummies\Plugins\DummyConsolePlugin;
use Rocketeer\TestCases\RocketeerTestCase;

class AbstractPluginTest extends RocketeerTestCase
{
    public function testCanGetPluginOption()
    {
        $this->config->set('plugins.config.foobar.foo', 'bar');

        $plugin = new DummyConsolePlugin();
        $plugin->setContainer($this->container);
        $this->assertEquals('bar', $plugin->getFooOption());
    }
}
