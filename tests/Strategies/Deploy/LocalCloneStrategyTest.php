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

use Carbon\Carbon;
use Rocketeer\TestCases\RocketeerTestCase;

class LocalCloneStrategyTest extends RocketeerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app['path.storage'] = $this->server.'/storage';

        $this->swapConnections([
            'production' => [
                'host' => 'bar.com',
                'username' => 'foo',
            ],
        ]);
    }

    public function testCanDeployRepository()
    {
        $time = $this->getCurrentTime();
        $this->pretend();

        $this->builder->buildStrategy('Deploy', 'LocalClone')->deploy();

        $matcher = [
            'mkdir {server}/releases/{release}',
            'git clone "https://github.com/Anahkiasen/html-object.git" "{storage}/checkout/tmp/'.$time.'/" --branch="master" --depth="1"',
            'rsync {storage}/checkout/tmp/'.$time.'/ foo@bar.com:{server}/releases/{release} --verbose --recursive --compress --rsh="ssh" --exclude=".git" --exclude="vendor"',
        ];

        $this->assertHistory($matcher);
    }

    public function testCanSpecifyKey()
    {
        $time = $this->getCurrentTime();
        $this->pretend();

        $this->swapConnections([
            'production' => [
                'username' => 'foo',
                'host' => 'bar.com:80',
                'key' => '/foo/bar',
            ],
        ]);

        $this->builder->buildStrategy('Deploy', 'LocalClone')->deploy();

        $matcher = [
            'mkdir {server}/releases/{release}',
            'git clone "https://github.com/Anahkiasen/html-object.git" "{storage}/checkout/tmp/'.$time.'/" --branch="master" --depth="1"',
            'rsync {storage}/checkout/tmp/'.$time.'/ foo@bar.com:{server}/releases/{release} --verbose --recursive --compress --rsh="ssh -p 80 -i /foo/bar" --exclude=".git" --exclude="vendor"',
        ];

        $this->assertHistory($matcher);
    }

    /**
     * Mock the current time.
     *
     * @return int
     */
    protected function getCurrentTime()
    {
        $now = Carbon::now();
        $time = $now->timestamp;

        Carbon::setTestNow($now);

        return $time;
    }
}
