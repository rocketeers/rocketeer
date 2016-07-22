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

namespace Rocketeer\TestCases\Modules;

use League\Flysystem\Filesystem;
use Prophecy\Prophecy\ObjectProphecy;
use Rocketeer\Console\Commands\AbstractCommand;
use Rocketeer\Console\StyleInterface;
use Rocketeer\Services\Connections\ConnectionsFactory;
use Rocketeer\Services\Filesystem\FilesystemInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @mixin \Rocketeer\TestCases\RocketeerTestCase
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait Mocks
{
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

    /**
     * Mock the RemoteHandler.
     *
     * @param string|array|null $expectations
     */
    protected function mockRemote($expectations = null)
    {
        $this->container->add(ConnectionsFactory::class, $this->getConnectionsFactory($expectations));
        $this->connections->disconnect();
    }

    /**
     * @param bool $withAdapter
     *
     * @return Filesystem
     */
    protected function bindFilesystemProphecy($withAdapter = false)
    {
        $adapter = $this->files->getAdapter();
        $prophecy = $this->bindProphecy(Filesystem::class);
        if ($withAdapter) {
            $prophecy->getAdapter()->willReturn($adapter);
        }

        return $prophecy;
    }
}
