<?php
namespace Rocketeer\Dummies\Plugins;

use Rocketeer\Abstracts\AbstractPlugin;
use Rocketeer\Console\Console;

class DummyConsolePlugin extends AbstractPlugin
{
    public function onConsole(Console $console)
    {
        $console->addCommands(array(
            new DummyPluginCommand(),
        ));
    }
}
