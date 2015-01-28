<?php
namespace Rocketeer\Services\Connections;

use Mockery\MockInterface;
use Rocketeer\TestCases\RocketeerTestCase;

class CoordinatorTest extends RocketeerTestCase
{
    public function testCanGetStatusOfServer()
    {
        $this->assertEquals(0, $this->coordinator->getStatus('production'));
        $this->events->fire('rocketeer.deploy.before-symlink');
        $this->assertEquals(1, $this->coordinator->getStatus('production'));
    }
}
