<?php
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
