<?php
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
