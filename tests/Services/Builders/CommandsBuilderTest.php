<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Builders;

use Rocketeer\Console\Commands\BaseTaskCommand;
use Rocketeer\Console\Commands\DeployCommand;
use Rocketeer\TestCases\RocketeerTestCase;

class CommandsBuilderTest extends RocketeerTestCase
{
    public function testCanCreateCommandOfTask()
    {
        $command = $this->builder->buildCommand('Deploy', 'lol');
        $this->assertInstanceOf(DeployCommand::class, $command);
        $this->assertEquals('deploy:deploy', $command->getName());

        $command = $this->builder->buildCommand('ls', 'ls');
        $this->assertInstanceOf(BaseTaskCommand::class, $command);
        $this->assertEquals('deploy:ls', $command->getName());
    }
}
