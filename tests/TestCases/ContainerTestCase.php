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

namespace Rocketeer\TestCases;

use League\Container\ContainerAwareTrait;
use League\Flysystem\MountManager;
use League\Flysystem\Vfs\VfsAdapter;
use Prophecy\Prophecy\ObjectProphecy;
use Rocketeer\Console\Commands\AbstractCommand;
use Rocketeer\Console\StyleInterface;
use Rocketeer\Services\Container\Container;
use Rocketeer\Services\Filesystem\Filesystem;
use Rocketeer\Services\Filesystem\FilesystemInterface;
use Rocketeer\Services\Storages\Storage;
use Rocketeer\TestCases\Modules\Assertions;
use Rocketeer\TestCases\Modules\Contexts;
use Rocketeer\TestCases\Modules\Mocks\ConfigurationTester;
use Rocketeer\TestCases\Modules\Mocks\ConnectionsTester;
use Rocketeer\TestCases\Modules\Mocks\ConsoleTester;
use Rocketeer\TestCases\Modules\Mocks\FilesystemTester;
use Rocketeer\TestCases\Modules\Mocks\TasksTester;
use Rocketeer\Traits\HasLocatorTrait;
use Symfony\Component\Console\Output\OutputInterface;
use VirtualFileSystem\FileSystem as Vfs;

abstract class ContainerTestCase extends BaseTestCase
{
    use Assertions;
    use ConfigurationTester;
    use ConnectionsTester;
    use ConsoleTester;
    use Contexts;
    use FilesystemTester;
    use TasksTester;

    use HasLocatorTrait;
    use ContainerAwareTrait;

    /**
     * Set up the tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->container = new Container();

        $this->setupContainer();
        $this->swapConfig();
    }

    /**
     * Setup the container with anything needed for tests.
     */
    protected function setupContainer()
    {
        $this->container->add('path.base', '/src');
        $this->container->add('paths.app', $this->container->get('path.base').'/app');

        // Bind new Storage instance
        $this->container->share('storage.local', function () {
            return new Storage($this->container, 'local', $this->server, 'deployments');
        });

        $this->files->setAdapter(new VfsAdapter(new Vfs()));
        $this->container->share(MountManager::class, function () {
            return new MountManager([
                'local' => $this->files,
                'remote' => $this->files,
            ]);
        });

        $this->bindDummyConnection();
        $this->bindDummyCommand();
    }

    /**
     * @param string|ObjectProphecy $class
     * @param string|null           $handle
     *
     * @return ObjectProphecy
     */
    protected function bindProphecy($class, $handle = null)
    {
        $prophecy = $class instanceof ObjectProphecy ? $class : $this->prophesize($class);
        switch ($class) {
            case Filesystem::class:
                $prophecy->willImplement(FilesystemInterface::class);
                break;
            case AbstractCommand::class:
                $handle = 'command';
                $prophecy
                    ->willImplement(StyleInterface::class)
                    ->willImplement(OutputInterface::class);
                break;
        }

        $handle = $handle ?: $class;

        if ($this->container->has($handle)) {
            $this->container->get($handle);
        }

        $this->container->add($handle, $prophecy->reveal());

        return $prophecy;
    }
}
