<?php
namespace Rocketeer\Services\Builders;

use Rocketeer\TestCases\RocketeerTestCase;

class BuilderTest extends RocketeerTestCase
{
    public function testCanAddLookups()
    {
        $this->builder->registerLookup('tasks', 'Rocketeer\Dummies\Tasks\%s');
        $task = $this->builder->buildTask('MyCustomTask');

        $this->assertInstanceOf('Rocketeer\Dummies\Tasks\MyCustomTask', $task);
    }

    public function testCanAddLookupsOfMultipleTypes()
    {
        $this->builder->registerLookups(['tasks' => 'Rocketeer\Dummies\Tasks\%s']);
        $task = $this->builder->buildTask('MyCustomTask');

        $this->assertInstanceOf('Rocketeer\Dummies\Tasks\MyCustomTask', $task);
    }
}
