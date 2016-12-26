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

namespace Rocketeer\Services\Environment\Modules;

use Rocketeer\TestCases\RocketeerTestCase;

class ServerPathfinderTest extends RocketeerTestCase
{
    public function testCanAppendStuffToFolders()
    {
        $folder = $this->paths->getReleasesFolder();
        $path = $this->paths->getReleasesFolder('foobar');

        $this->assertEquals($folder.'/foobar', $path);
    }
}
