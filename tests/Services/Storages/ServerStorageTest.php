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

use Rocketeer\TestCases\RocketeerTestCase;

class ServerStorageTest extends RocketeerTestCase
{
    public function testCanDestroyRemoteFile()
    {
        $server = new ServerStorage($this->app, 'test');
        $file = $server->getFilepath();
        $server->destroy();

        $this->assertFileNotExists($file);
    }

    public function testDoesntWriteInPretendMode()
    {
        $this->pretend();

        $storage = new ServerStorage($this->app, 'state');
        $before = $this->files->get($storage->getFilepath());
        $storage->set('foo', 'bar');

        $this->assertEquals($before, $this->files->get($storage->getFilepath()));
    }
}
