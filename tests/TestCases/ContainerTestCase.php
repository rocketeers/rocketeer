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
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use League\Flysystem\Vfs\VfsAdapter;
use PHPUnit_Framework_TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Rocketeer\Console\Commands\AbstractCommand;
use Rocketeer\Console\StyleInterface;
use Rocketeer\Container;
use Rocketeer\Services\Connections\ConnectionsFactory;
use Rocketeer\Services\Filesystem\FilesystemInterface;
use Rocketeer\Services\Filesystem\Plugins\AppendPlugin;
use Rocketeer\Services\Filesystem\Plugins\CopyDirectoryPlugin;
use Rocketeer\Services\Filesystem\Plugins\IncludePlugin;
use Rocketeer\Services\Filesystem\Plugins\IsDirectoryPlugin;
use Rocketeer\Services\Filesystem\Plugins\RequirePlugin;
use Rocketeer\Services\Filesystem\Plugins\UpsertPlugin;
use Rocketeer\Services\Storages\Storage;
use Rocketeer\TestCases\Modules\Assertions;
use Rocketeer\TestCases\Modules\Building;
use Rocketeer\TestCases\Modules\Contexts;
use Rocketeer\TestCases\Modules\Mocks\Configuration;
use Rocketeer\TestCases\Modules\Mocks\Connections;
use Rocketeer\TestCases\Modules\Mocks\Console;
use Rocketeer\Traits\HasLocatorTrait;
use Symfony\Component\Console\Output\OutputInterface;
use VirtualFileSystem\FileSystem as Vfs;

abstract class ContainerTestCase extends PHPUnit_Framework_TestCase
{
    use Assertions;
    use Building;
    use Configuration;
    use Console;
    use Connections;
    use \Rocketeer\TestCases\Modules\Mocks\Filesystem;
    use Contexts;

    use HasLocatorTrait;
    use ContainerAwareTrait;

    /**
     * The path to the local fake server.
     *
     * @var string
     */
    protected $server;

    /**
     * @var string
     */
    protected $home;

    /**
     * Set up the tests.
     */
    public function setUp()
    {
        $this->container = new Container();

        // Create local paths
        $this->home = $_SERVER['HOME'];
        $this->server = realpath(__DIR__.'/../_server').'/foobar';

        $this->setupContainer();
        $this->swapConfigWithEvents();
    }

    /**
     * Setup the container with anything needed for tests.
     */
    protected function setupContainer()
    {
        $this->container->add('path.base', '/src');
        $this->container->add('paths.app', $this->container->get('path.base').'/app');

        // Replace some instances with mocks
        $this->container->share(ConnectionsFactory::class, function () {
            return $this->getConnectionsFactory();
        });

        // Bind new Storage instance
        $this->container->share('storage.local', function () {
            return new Storage($this->container, 'local', $this->server, 'deployments');
        });

        $this->bindDummyCommand();

        $filesystem = new Filesystem(new VfsAdapter(new Vfs()));
        $filesystem->addPlugin(new AppendPlugin());
        $filesystem->addPlugin(new CopyDirectoryPlugin());
        $filesystem->addPlugin(new IncludePlugin());
        $filesystem->addPlugin(new IsDirectoryPlugin());
        $filesystem->addPlugin(new RequirePlugin());
        $filesystem->addPlugin(new UpsertPlugin());

        $this->container->add(Filesystem::class, $filesystem);
        $this->container->share(MountManager::class, function () use ($filesystem) {
            return new MountManager([
                'local' => $filesystem,
                'remote' => $filesystem,
            ]);
        });
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
