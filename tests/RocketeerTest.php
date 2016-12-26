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

namespace Rocketeer;

use Rocketeer\TestCases\RocketeerTestCase;

class RocketeerTest extends RocketeerTestCase
{
    public function testCanUseRecursiveStageConfiguration()
    {
        $this->swapConfig([
            'vcs.branch' => 'master',
            'on.stages.staging.vcs.branch' => 'staging',
        ]);

        $this->assertOptionValueEquals('master', 'vcs.branch');
        $this->connections->setStage('staging');
        $this->assertOptionValueEquals('staging', 'vcs.branch');
    }

    public function testCanUseRecursiveConnectionConfiguration()
    {
        $this->swapConfig([
            'default' => 'production',
            'vcs.branch' => 'master',
            'on.connections.staging.vcs.branch' => 'staging',
        ]);
        $this->assertOptionValueEquals('master', 'vcs.branch');

        $this->swapConfig([
            'default' => 'staging',
            'vcs.branch' => 'master',
            'on.connections.staging.vcs.branch' => 'staging',
        ]);
        $this->assertOptionValueEquals('staging', 'vcs.branch');
    }

    public function testRocketeerCanGuessWhichStageHesIn()
    {
        $path = '/home/www/foobar/production/releases/12345678901234/app';
        $stage = Rocketeer::getDetectedStage('foobar', $path);
        $this->assertEquals('production', $stage);

        $path = '/home/www/foobar/staging/releases/12345678901234/app';
        $stage = Rocketeer::getDetectedStage('foobar', $path);
        $this->assertEquals('staging', $stage);

        $path = '/home/www/foobar/releases/12345678901234/app';
        $stage = Rocketeer::getDetectedStage('foobar', $path);
        $this->assertEquals(false, $stage);
    }

    public function testCanUserServerContextualConfiguration()
    {
        $this->config->set('connections.production.servers.0.config.remote.directories.app_directory', '/foo/bar');

        $this->assertEquals('/foo/bar', $this->config->getContextually('remote.directories.app_directory'));
    }
}
