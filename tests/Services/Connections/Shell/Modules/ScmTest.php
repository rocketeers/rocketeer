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

namespace Rocketeer\Services\Connections\Shell\Modules;

use Mockery\MockInterface;
use Rocketeer\Services\Connections\Shell\Bash;
use Rocketeer\TestCases\RocketeerTestCase;

class ScmTest extends RocketeerTestCase
{
    public function testCanForgetCredentialsIfInvalid()
    {
        $this->container->get('storage.local')->set('credentials', [
            'repository' => 'https://bitbucket.org/Anahkiasen/registry.git',
            'username' => 'Anahkiasen',
            'password' => 'baz',
        ]);

        $this->mock(Bash::class, 'Bash', function (MockInterface $mock) {
            return $mock
                ->shouldIgnoreMissing()
                ->shouldReceive('checkStatus')->andReturn(false);
        });

        $task = $this->pretendTask();

        $task->getStrategy('Deploy')->deploy($this->server.'/test');
        $this->assertNull($this->container->get('storage.local')->get('credentials'));
    }
}
