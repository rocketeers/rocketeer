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

namespace Rocketeer\Services\Storages;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\Sftp\SftpAdapter;
use Rocketeer\Container;
use Rocketeer\Services\Config\Configuration;
use Rocketeer\Services\Config\ContextualConfiguration;
use Rocketeer\TestCases\RocketeerTestCase;

class StorageTest extends RocketeerTestCase
{
    public function testCanSwapContents()
    {
        $matcher = ['foo' => 'caca'];
        $this->localStorage->set($matcher);
        $contents = $this->localStorage->get('foo');

        $this->assertEquals('caca', $contents);
    }

    public function testCanSetValue()
    {
        $this->localStorage->set('foo', 'baz');

        $this->assertEquals('baz', $this->localStorage->get('foo'));
    }

    public function testCanDestroy()
    {
        $this->localStorage->set('foo', 'bar');
        $this->localStorage->destroy();

        $this->assertNull($this->localStorage->get('foo'));
    }

    public function testDoesntTryToDestroyTwice()
    {
        $this->localStorage->destroy();
        $this->localStorage->destroy();
    }

    public function testCanFallbackIfFileDoesntExist()
    {
        $this->localStorage->destroy();

        $this->assertEquals(null, $this->localStorage->get('foo'));
    }

    public function testUsesLocalFilesystemIfLocalMode()
    {
        $this->rocketeer->setLocal(true);
        $this->filesystems->mountFilesystem('remote', new Filesystem(new SftpAdapter([])));

        $storage = new ServerStorage($this->container);
        $this->assertInstanceOf(Local::class, $storage->getFilesystem()->getAdapter());
    }

    public function testAccessFilesClassDirectlyIfLocal()
    {
        $this->rocketeer->setLocal(true);

        $storage = new ServerStorage($this->container);
        $this->assertInstanceOf(Local::class, $storage->getFilesystem()->getAdapter());
    }
}
