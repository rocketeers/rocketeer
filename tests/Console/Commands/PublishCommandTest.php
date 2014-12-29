<?php
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
