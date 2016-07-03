<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Filesystem;

use League\Flysystem\Filesystem;
use Rocketeer\TestCases\RocketeerTestCase;

class FilesystemMounterTest extends RocketeerTestCase
{
    public function testMountsLocalAndRemoteFilesystems()
    {
        $mounter = new FilesystemsMounter($this->container);
        $manager = $mounter->getMountManager();

        $local = $manager->getFilesystem('local');
        $remote = $manager->getFilesystem('remote');

        $this->assertInstanceOf(Filesystem::class, $local);
        $this->assertInstanceOf(Filesystem::class, $remote);
    }
}
