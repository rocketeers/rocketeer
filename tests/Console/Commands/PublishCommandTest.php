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
        $this->usesLaravel(false);

        $tester = $this->executeCommand('plugins:config', ['package' => 'foo/bar']);
        $this->assertContains('No configuration found', $tester->getDisplay());
    }

    public function testCanExportConfiguration()
    {
        $path = $this->paths->getRocketeerPath().'/vendor/anahkiasen/rocketeer-slack/config';
        $this->files->put($path.'/rocketeer-slack.php', '<?php return ["foo" => "bar"]');

        $tester = $this->executeCommand('plugins:config', ['package' => 'anahkiasen/rocketeer-slack']);
        $this->assertContains('Published', $tester->getDisplay());
        $this->assertVirtualFileExists($this->paths->getRocketeerPath().'/config/plugins/rocketeer-slack.php');
    }
}
