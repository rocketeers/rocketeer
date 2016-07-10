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

namespace Rocketeer\Binaries;

use Rocketeer\TestCases\RocketeerTestCase;

class PhpTest extends RocketeerTestCase
{
    /**
     * @dataProvider providesHhvmStatus
     *
     * @param int $defined
     */
    public function testCanCheckIfUsesHhvm($defined)
    {
        $this->mockRemote([
            'which php' => 'php',
            'php -r "print defined(\'HHVM_VERSION\');"' => $defined,
        ]);

        $this->assertEquals($defined, $this->bash->php()->isHhvm());
    }

    /**
     * @return array
     */
    public function providesHhvmStatus()
    {
        return [
            [1],
            [0],
        ];
    }
}
