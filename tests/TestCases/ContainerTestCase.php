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
use Prophecy\Argument;
use Rocketeer\Container;
use Rocketeer\Dummies\Connections\DummyConnection;
use Rocketeer\Services\Connections\ConnectionsFactory;
use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;
use Rocketeer\Services\Filesystem\Plugins\AppendPlugin;
use Rocketeer\Services\Filesystem\Plugins\CopyDirectoryPlugin;
use Rocketeer\Services\Filesystem\Plugins\IncludePlugin;
use Rocketeer\Services\Filesystem\Plugins\IsDirectoryPlugin;
use Rocketeer\Services\Filesystem\Plugins\RequirePlugin;
use Rocketeer\Services\Filesystem\Plugins\UpsertPlugin;
use Rocketeer\TestCases\Modules\Assertions;
use Rocketeer\TestCases\Modules\Building;
use Rocketeer\TestCases\Modules\Configuration;
use Rocketeer\TestCases\Modules\Contexts;
use Rocketeer\TestCases\Modules\Mocks;
use Rocketeer\Traits\HasLocatorTrait;
use VirtualFileSystem\FileSystem as Vfs;

abstract class ContainerTestCase extends PHPUnit_Framework_TestCase
{
    use Mocks;
    use Assertions;
    use Contexts;
    use Configuration;
    use Building;
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

        // Paths -------------------------------------------------------- /

        $this->container->add('path.base', '/src');
        $this->container->add('paths.app', $this->container->get('path.base').'/app');

        // Replace some instances with mocks
        $this->container->share(ConnectionsFactory::class, function () {
            return $this->getConnectionsFactory();
        });

        $this->mockCommand();

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

        $this->swapConfigWithEvents();
    }

    ////////////////////////////////////////////////////////////////////
    ///////////////////////// MOCKED INSTANCES /////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * @param string|array $expectations
     *
     * @return MockInterface
     */
    protected function getConnectionsFactory($expectations = null)
    {
        $me = $this;

        /** @var ConnectionsFactory $factory */
        $factory = $this->prophesize(ConnectionsFactory::class);
        $factory->make(Argument::type(ConnectionKey::class))->will(function ($arguments) use ($me, $expectations) {
            $connection = new DummyConnection($arguments[0]);
            $connection->setExpectations($expectations);
            if ($adapter = $me->files->getAdapter()) {
                $connection->setAdapter($adapter);
            }

            return $connection;
        });

        return $factory->reveal();
    }
}
