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

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Rocketeer\Container;
use Rocketeer\TestCases\RocketeerTestCase;

class MountManagerFactoryTest extends RocketeerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->container = new Container();
        $this->swapConnections([
            'production' => [
                'host' => 'foo.com',
            ],
        ]);
    }

    public function testCanMountConnectionsAsFilesystems()
    {
        $production = $this->filesystems->getFilesystem('production');
        $this->assertInstanceOf(Filesystem::class, $production);
    }

    public function testMountsLocalAndRemoteFilesystems()
    {
        $this->connections->setCurrentConnection('production');

        $local = $this->filesystems->getFilesystem('local');
        $remote = $this->filesystems->getFilesystem('remote');

        $this->assertInstanceOf(Filesystem::class, $local);
        $this->assertInstanceOf(Filesystem::class, $remote);
    }

    public function testCanChangesConnectionAfterBoot()
    {
        $production = $this->filesystems->getFilesystem('production');
        $this->assertInstanceOf(Filesystem::class, $production);

        $this->swapConnections([
            'foobar' => ['host' => 'foo.com'],
        ]);

        $production = $this->filesystems->getFilesystem('foobar');
        $this->assertInstanceOf(Filesystem::class, $production);
    }

    public function testCanBindAdditionalInstancesOnMountManager()
    {
        $this->container = new Container();
        $this->filesystems->mountFilesystem('dropbox', new Filesystem(new Local('/')));

        $this->assertInstanceOf(FilesystemInterface::class, $this->filesystems->getFilesystem('dropbox'));
    }
}
