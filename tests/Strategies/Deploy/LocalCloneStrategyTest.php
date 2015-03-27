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

class LocalCloneStrategyTest extends RocketeerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->swapConfig([
            'rocketeer::connections' => [
                'production' => [
                    'host'     => 'bar.com',
                    'username' => 'foo',
                ],
            ],
        ]);
    }

    public function testCanDeployRepository()
    {
        $task = $this->pretendTask('Deploy');
        $task->getStrategy('Deploy', 'LocalClone')->deploy();

        // We just have to pray that second do not change during test
        $time = time();

        $matcher = [
            'mkdir {server}/releases/{release}',
            'git clone "https://github.com/Anahkiasen/html-object.git" "app/storage/checkout/tmp/'.$time.'/" --branch="master" --depth="1"',
            'rsync app/storage/checkout/tmp/'.$time.'/ foo@bar.com:{server}/releases/{release} --verbose --recursive --rsh="ssh" --compress --exclude=".git" --exclude="vendor"',
        ];

        $this->assertHistory($matcher);
    }

    public function testCanSpecifyKey()
    {
        $this->swapConfig([
            'rocketeer::connections' => [
                'production' => [
                    'username' => 'foo',
                    'host'     => 'bar.com:80',
                    'key'      => '/foo/bar',
                ],
            ],
        ]);

        $task = $this->pretendTask('Deploy');
        $task->getStrategy('Deploy', 'LocalClone')->deploy();

        // We just have to pray that second do not change during test
        $time = time();

        $matcher = [
            'mkdir {server}/releases/{release}',
            'git clone "https://github.com/Anahkiasen/html-object.git" "app/storage/checkout/tmp/'.$time.'/" --branch="master" --depth="1"',
            'rsync app/storage/checkout/tmp/'.$time.'/ foo@bar.com:{server}/releases/{release} --verbose --recursive --rsh="ssh -p 80 -i /foo/bar" --compress --exclude=".git" --exclude="vendor"',
        ];

        $this->assertHistory($matcher);
    }
}
