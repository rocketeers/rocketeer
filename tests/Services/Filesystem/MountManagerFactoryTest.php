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

namespace Rocketeer\Services\Filesystem;

use Rocketeer\TestCases\RocketeerTestCase;

class MountManagerFactoryTest extends RocketeerTestCase
{
    public function testCanMountConnectionsAsFilesystems()
    {
        $mounter = new MountManagerFactory($this->container);
        $manager = $mounter->getMountManager();

        $production = $manager->getFilesystem('production');
        $this->assertInstanceOf(Filesystem::class, $production);
    }

    public function testMountsLocalAndRemoteFilesystems()
    {
        $this->connections->setCurrentConnection('production');

        $mounter = new MountManagerFactory($this->container);
        $manager = $mounter->getMountManager();

        $local = $manager->getFilesystem('local');
        $remote = $manager->getFilesystem('remote');

        $this->assertInstanceOf(Filesystem::class, $local);
        $this->assertInstanceOf(Filesystem::class, $remote);
    }
}
