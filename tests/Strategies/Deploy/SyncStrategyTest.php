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

namespace Rocketeer\Strategies\Deploy;

use Rocketeer\Services\Connections\Shell\Bash;
use Rocketeer\TestCases\RocketeerTestCase;

class SyncStrategyTest extends RocketeerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->swapConfigWithEvents([
            'hooks' => [],
            'vcs.submodules' => false,
            'strategies.deploy' => 'Sync',
            'connections' => [
                'production' => [
                    'host' => 'bar.com',
                    'username' => 'foo',
                    'root_directory' => $this->server,
                ],
            ],
        ]);

        $this->bash->on('dummy', function (Bash $task) {
            $task->createFolder($task->paths->getFolder('current'));
        });
    }

    public function testCanDeployRepository()
    {
        $this->pretendTask('Deploy')->fire();
        $this->assertRsyncHistory();
    }

    public function testCanUpdateRepository()
    {
        $this->pretendTask('Deploy', ['--update' => true])->fire();
        $this->assertRsyncHistory();
    }

    public function testCanSpecifyPortViaHostname()
    {
        $this->config->set('connections.production.host', 'bar.com:12345');
        $this->pretendTask('Deploy')->fire();

        $this->assertRsyncHistory(12345);
    }

    public function testCanSpecifyPortViaOptions()
    {
        $this->pretendTask('Deploy', ['--port' => 12345])->fire();
        $this->assertRsyncHistory(12345);
    }

    public function testCanSpecifyExcludedDirectories()
    {
        $this->pretendTask('Deploy')
            ->configure(['excluded' => ['foobar']])
            ->fire();

        $this->assertRsyncHistory(null, null, ['foobar']);
    }

    public function testCanSpecifyKey()
    {
        $this->config->set('connections.production.host', 'bar.com:80');
        $this->config->set('connections.production.key', '/foo bar/baz');

        $this->pretendTask('Deploy', ['--port' => 80])->fire();
        $this->assertRsyncHistory(80, '/foo bar/baz');
    }

    protected function assertRsyncHistory($port = null, $key = null, $exclude = ['.git', 'vendor'])
    {
        $port = $port ? ' -p '.$port : null;
        $key = $key ? ' -i "'.$key.'"' : null;
        $exclude = array_map(function ($file) {
            return '--exclude="'.$file.'"';
        }, $exclude);

        $this->assertHistoryContains('{rsync} /tmp/rocketeer/foobar/releases/{release} foo@bar.com:{server}/foobar/releases/{release} --verbose --recursive --compress --rsh="ssh'.$port.$key.'" '.implode(' ', $exclude));
    }
}
