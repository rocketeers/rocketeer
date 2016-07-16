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
        $this->localStorage->clear();

        $this->assertNull($this->localStorage->get('foo'));
    }

    public function testDoesntTryToDestroyTwice()
    {
        $this->localStorage->clear();
        $this->localStorage->clear();
    }

    public function testCanFallbackIfFileDoesntExist()
    {
        $this->localStorage->clear();

        $this->assertEquals(null, $this->localStorage->get('foo'));
    }

    public function testUsesLocalFilesystemIfLocalMode()
    {
        $this->rocketeer->setLocal(true);
        $this->filesystems->mountFilesystem('remote', new Filesystem(new SftpAdapter([])));

        $this->assertInstanceOf(Local::class, $this->remoteStorage->getFilesystem()->getAdapter());
    }

    public function testAccessFilesClassDirectlyIfLocal()
    {
        $this->rocketeer->setLocal(true);

        $this->assertInstanceOf(Local::class, $this->remoteStorage->getFilesystem()->getAdapter());
    }
}
