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

use Prophecy\Argument;
use Rocketeer\Services\Connections\Shell\Bash;
use Rocketeer\TestCases\RocketeerTestCase;

class ScmTest extends RocketeerTestCase
{
    public function testCanForgetCredentialsIfInvalid()
    {
        $this->localStorage->set('credentials', [
            'repository' => 'https://bitbucket.org/Anahkiasen/registry.git',
            'username' => 'Anahkiasen',
            'password' => 'baz',
        ]);

        $prophecy = $this->bindProphecy(Bash::class);
        $prophecy->run(Argument::cetera())->willReturn();
        $prophecy->displayStatusMessage(Argument::cetera())->willReturn(false);

        $task = $this->pretendTask();

        $task->getStrategy('Deploy')->deploy($this->server.'/test');
        $this->assertNull($this->localStorage->get('credentials'));
    }
}
