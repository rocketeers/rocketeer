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

namespace Rocketeer\Services\Builders;

use Rocketeer\Dummies\Tasks\MyCustomTask;
use Rocketeer\TestCases\RocketeerTestCase;

class BuilderTest extends RocketeerTestCase
{
    public function testCanAddLookups()
    {
        $this->builder->registerLookup('tasks', 'Rocketeer\Dummies\Tasks\%s');
        $task = $this->builder->buildTask('MyCustomTask');

        $this->assertInstanceOf(MyCustomTask::class, $task);
    }

    public function testCanAddLookupsOfMultipleTypes()
    {
        $this->builder->registerLookups(['tasks' => 'Rocketeer\Dummies\Tasks\%s']);
        $task = $this->builder->buildTask('MyCustomTask');

        $this->assertInstanceOf(MyCustomTask::class, $task);
    }

    public function testDoesntRegisterSameLookupTwice()
    {
        $this->builder->registerLookup('tasks', 'Rocketeer\Dummies\Tasks\%s');
        $this->builder->registerLookup('tasks', 'Rocketeer\Dummies\Tasks\%s');

        $this->assertEquals([
            'Rocketeer\Tasks\%s',
            'Rocketeer\Tasks\Subtasks\%s',
            '{applicationName}\Tasks\%s',
            'Rocketeer\Dummies\Tasks\%s',
        ], $this->builder->getLookups('tasks'));
    }
}
