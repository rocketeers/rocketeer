<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Storages;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\Sftp\SftpAdapter;
use Rocketeer\Container;
use Rocketeer\Services\Config\Configuration;
use Rocketeer\Services\Environment\EnvironmentServiceProvider;
use Rocketeer\TestCases\RocketeerTestCase;

class StorageTest extends RocketeerTestCase
{
    public function testCanInferStorageName()
    {
        $container = new Container();
        $container->add('path.base', __DIR__);
        $container->add('config', new Configuration([
            'config' => [
                'application_name' => '{application_name}',
            ],
        ]));

        $container->addServiceProvider(new EnvironmentServiceProvider());
        $container->addServiceProvider(new StorageServiceProvider());

        /** @var Storage $storage */
        $storage = $container->get('storage.local');

        $this->assertEquals('storages.json', $storage->getFilename());
    }

    public function testCanNormalizeFilename()
    {
        $this->localStorage->setFilename('foo/Bar.json');

        $this->assertEquals('bar.json', $this->localStorage->getFilename());
    }

    public function testCanSwapContents()
    {
        $matcher = ['foo' => 'caca'];
        $this->localStorage->set($matcher);
        $contents = $this->localStorage->get();
        unset($contents['hash']);

        $this->assertEquals($matcher, $contents);
    }

    public function testCanGetValue()
    {
        $this->assertEquals('bar', $this->localStorage->get('foo'));
    }

    public function testCanSetValue()
    {
        $this->localStorage->set('foo', 'baz');

        $this->assertEquals('baz', $this->localStorage->get('foo'));
    }

    public function testCanDestroy()
    {
        $this->localStorage->destroy();

        $this->assertFalse($this->files->has($this->localStorage->getFilepath()));
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
        $this->flysystem->mountFilesystem('remote', new Filesystem(new SftpAdapter([])));

        $storage = new ServerStorage($this->app);
        $this->assertInstanceOf(Local::class, $storage->getFilesystem()->getAdapter());
    }

    public function testAccessFilesClassDirectlyIfLocal()
    {
        $this->rocketeer->setLocal(true);
        $this->app->remove('flysystem');

        $storage = new ServerStorage($this->app);
        $this->assertInstanceOf(Local::class, $storage->getFilesystem()->getAdapter());
    }
}
