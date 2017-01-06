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

use Carbon\Carbon;
use Rocketeer\TestCases\RocketeerTestCase;

class LocalCloneStrategyTest extends RocketeerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->swapConfig([
            'rocketeer::connections' => [
                'production' => [
                    'host' => 'bar.com',
                    'username' => 'foo',
                ],
            ],
        ]);
    }

    public function testCanDeployRepository()
    {
        $time = $this->getCurrentTime();

        $task = $this->pretendTask('Deploy');
        $task->getStrategy('Deploy', 'LocalClone')->deploy();

        $matcher = [
            'mkdir {server}/releases/{release}',
            'git clone "https://github.com/Anahkiasen/html-object.git" "app/storage/checkout/tmp/'.$time.'/" --branch="master" --depth="1"',
            'rsync app/storage/checkout/tmp/'.$time.'/ foo@bar.com:{server}/releases/{release} --verbose --recursive --rsh="ssh" --compress --exclude=".git" --exclude="vendor"',
        ];

        $this->assertHistory($matcher);
    }

    public function testCanSpecifyKey()
    {
        $time = $this->getCurrentTime();

        $this->swapConfig([
            'rocketeer::connections' => [
                'production' => [
                    'username' => 'foo',
                    'host' => 'bar.com:80',
                    'key' => '/foo/bar',
                ],
            ],
        ]);

        $task = $this->pretendTask('Deploy');
        $task->getStrategy('Deploy', 'LocalClone')->deploy();

        $matcher = [
            'mkdir {server}/releases/{release}',
            'git clone "https://github.com/Anahkiasen/html-object.git" "app/storage/checkout/tmp/'.$time.'/" --branch="master" --depth="1"',
            'rsync app/storage/checkout/tmp/'.$time.'/ foo@bar.com:{server}/releases/{release} --verbose --recursive --rsh="ssh -p 80 -i /foo/bar" --compress --exclude=".git" --exclude="vendor"',
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
        Carbon::setTestNow(new Carbon(1234567890));
        $time = Carbon::now()->timestamp;

        return $time;
    }
}
