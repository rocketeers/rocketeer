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

namespace Rocketeer\Services\Connections\Credentials\Keys;

use Rocketeer\TestCases\RocketeerTestCase;

class RepositoryKeyTest extends RocketeerTestCase
{
    /**
     * @dataProvider provideEndpoints
     *
     * @param string $endpoint
     */
    public function testCanGetRepositoryName($endpoint)
    {
        $key = new RepositoryKey([
            'repository' => $endpoint,
            'username' => 'anahkiasen',
        ]);

        $this->assertEquals('rocketeers/rocketeer', $key->getName());
    }

    /**
     * @return array
     */
    public function provideEndpoints()
    {
        return [
            ['git@github.com:rocketeers/rocketeer.git'],
            ['https://github.com/rocketeers/rocketeer.git'],
        ];
    }
}
