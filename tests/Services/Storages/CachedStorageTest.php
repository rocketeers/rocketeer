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

use Mockery\MockInterface;
use Rocketeer\Services\Environment\Pathfinder;
use Rocketeer\TestCases\RocketeerTestCase;

class CachedStorageTest extends RocketeerTestCase
{
    public function testCanComputeHashAccordingToContentsOfFiles()
    {
        $this->mock(Pathfinder::class, Pathfinder::class, function (MockInterface $mock) {
            return $mock->shouldReceive('getConfigurationPath')->andReturn($this->server);
        });

        $this->files->createDir($this->server.'/tasks');
        $this->files->createDir($this->server.'/strategies');
        $this->files->put($this->server.'/bar.php', '<?php return ["bar"];');
        $this->files->put($this->server.'/foo.php', '<?php return ["foo"];');
        $this->files->put($this->server.'/strategies.php', '<?php return ["baz"];');
        $this->files->put($this->server.'/tasks.php', '<?php return ["tasks"];');
        $this->files->put($this->server.'/tasks/test123r.php', '<?php return ["tasks"];');
        $this->files->put($this->server.'/strategies/MyStrategy.php', '<?php return ["strategies"];');

        $storage = new CachedStorage($this->localStorage, $this->server);
        $hash = $storage->getHash();

        $this->assertEquals(md5('["bar"]["foo"]["baz"]'), $hash);
    }
}
