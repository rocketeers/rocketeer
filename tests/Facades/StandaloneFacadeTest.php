<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Facades;

use League\Flysystem\Filesystem;
use Rocketeer\Container;
use Rocketeer\Services\Storages\Storage;
use Rocketeer\TestCases\RocketeerTestCase;

class StandaloneFacadeTest extends RocketeerTestCase
{
    public function testCanWrapAppInFacade()
    {
        $container = \Rocketeer\Facades\Rocketeer::getContainer();
        $this->assertInstanceOf(Container::class, $container);

        $storage = $container->get('rocketeer.storage.local');
        $this->assertInstanceOf(Storage::class, $storage);
        $this->assertInstanceOf(Filesystem::class, $storage->getFilesystem());
    }
}
