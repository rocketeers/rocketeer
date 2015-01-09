<?php
namespace Rocketeer\Services\Display;

use Rocketeer\TestCases\RocketeerTestCase;

class QueueTimerTest extends RocketeerTestCase
{
    public function testCanTimeTasks()
    {
        $task = $this->builder->buildTask('ls');
        $this->timer->time($task, function () use ($task) {
            $task->fire();
        });

        $time = (string) $this->timer->getTaskTime($task);
        $this->assertRegExp('#\d\.\d{2}#', $time);
    }

    public function testDoesntSaveTimeOfPretendTasks()
    {
        $task = $this->pretendTask();
        $this->timer->time($task, function () use ($task) {
            $task->fire();
        });

        $time = $this->timer->getTaskTime($task);
        $this->assertNull($time);

    }
}
