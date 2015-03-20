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

use Rocketeer\TestCases\RocketeerTestCase;

class ServerStorageTest extends RocketeerTestCase
{
    public function testCanDestroyRemoteFile()
    {
        $server = new ServerStorage($this->app, 'test');
        $file   = $server->getFilepath();
        $server->destroy();

        $this->assertVirtualFileNotExists($file);
    }

    public function testDoesntWriteInPretendMode()
    {
        $this->pretend();

        $server = new ServerStorage($this->app, 'state');
        $server->set('foo', 'bar');

        $this->assertNull($server->get('foo'));
    }
}
