<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Dummies\Plugins;

use Rocketeer\Abstracts\AbstractPlugin;
use Rocketeer\Console\Console;

class DummyConsolePlugin extends AbstractPlugin
{
    public function onConsole(Console $console)
    {
        $console->addCommands([
            new DummyPluginCommand(),
        ]);
    }
}
