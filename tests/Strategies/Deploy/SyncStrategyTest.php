<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Strategies\Deploy;

use Rocketeer\TestCases\RocketeerTestCase;

class SyncStrategyTest extends RocketeerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->swapConnections([
            'production' => [
                'host' => 'bar.com',
                'username' => 'foo',
            ],
        ]);
    }

    public function testCanDeployRepository()
    {
        $task = $this->pretendTask('Deploy');
        $task->getStrategy('Deploy', 'Sync')->deploy();

        $this->assertRsyncHistory(null, null, [
            'mkdir {server}/releases/{release}',
        ]);
    }

    public function testCanUpdateRepository()
    {
        $task = $this->pretendTask('Deploy');
        $task->getStrategy('Deploy', 'Sync')->update();

        $this->assertRsyncHistory();
    }

    public function testCanSpecifyPortViaHostname()
    {
        $this->swapConnections([
            'production' => [
                'host' => 'bar.com:12345',
                'username' => 'foo',
            ],
        ]);

        $task = $this->pretendTask('Deploy');
        $task->getStrategy('Deploy', 'Sync')->update();

        $this->assertRsyncHistory(12345);
    }

    public function testCanSpecifyPortViaOptions()
    {
        $task = $this->pretendTask('Deploy');
        $task->getStrategy('Deploy', 'Sync', ['port' => 12345])->update();

        $this->assertRsyncHistory(12345);
    }

    public function testCanSpecifyKey()
    {
        $this->swapConnections([
            'production' => [
                'username' => 'foo',
                'host' => 'bar.com:80',
                'key' => '/foo/bar',
            ],
        ]);

        $task = $this->pretendTask('Deploy');
        $task->getStrategy('Deploy', 'Sync', ['port' => 80])->update();

        $this->assertRsyncHistory(80, '/foo/bar');
    }

    protected function assertRsyncHistory($port = null, $key = null, $prepend = [])
    {
        $port = $port ? ' -p '.$port : null;
        $key = $key ? ' -i '.$key : null;

        $matcher = array_merge($prepend, [
            'rsync ./ foo@bar.com:{server}/releases/{release} --verbose --recursive --compress --rsh="ssh'.$port.$key.'" --exclude=".git" --exclude="vendor"',
        ]);

        $this->assertHistory($matcher);
    }
}
