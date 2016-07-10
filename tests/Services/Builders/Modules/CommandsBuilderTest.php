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

namespace Rocketeer\Services\Builders\Modules;

use Rocketeer\Console\Commands\BaseTaskCommand;
use Rocketeer\Console\Commands\DeployCommand;
use Rocketeer\TestCases\RocketeerTestCase;

class CommandsBuilderTest extends RocketeerTestCase
{
    /**
     * @dataProvider providesCommands
     *
     * @param string $command
     * @param string $name
     * @param string $class
     * @param string $slug
     */
    public function testCanCreateCommandOfTask($command, $name, $class, $slug)
    {
        $command = $this->builder->buildCommand($command, $name);
        $this->assertInstanceOf($class, $command);
        $this->assertEquals($slug, $command->getName());
    }

    /**
     * @return array
     */
    public function providesCommands()
    {
        return [
            'Existing task' => ['Deploy', 'lol', DeployCommand::class, 'deploy'],
            'Custom task' => ['ls', 'ls', BaseTaskCommand::class, 'ls'],
        ];
    }
}
