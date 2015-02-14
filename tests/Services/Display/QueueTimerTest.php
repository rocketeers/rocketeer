<?php
namespace Rocketeer\Services\Display;

use Rocketeer\Console\Commands\DeployCommand;
use Rocketeer\TestCases\RocketeerTestCase;

class QueueTimerTest extends RocketeerTestCase
{
    public function testCanTimeTasks()
    {
        $task = $this->task('ls');
        $this->timer->time($task, function () use ($task) {
            $task->fire();
        });

        $time = (string) $this->timer->getTime($task);
        $this->assertTime($time);
    }

    public function testDoesntSaveTimeOfPretendTasks()
    {
        $task = $this->pretendTask();
        $this->timer->time($task, function () use ($task) {
            // ...
        });

        $time = $this->timer->getTime($task);
        $this->assertNull($time);
    }

    public function testCanTimeCommands()
    {
        $command = new DeployCommand();
        $command->setLaravel($this->app);
        $this->timer->time($command, function () {
            // ...
        });

        $time = $this->timer->getTime($command);
        $this->assertTime($time);
    }

    public function testCanGetLastRecordedTime()
    {
        $task = $this->task('ls');
        $this->timer->time($task, function () use ($task) {
            $task->fire();
        });
        $this->timer->time($task, function () use ($task) {
            $task->fire();
        });

        $times = $this->timer->getTimes($task);
        $last  = $this->timer->getLatestTime($task);

        $this->assertEquals($last, $times[3]);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param string $time
     */
    protected function assertTime($time)
    {
        $this->assertRegExp('#(\d\.\d{1,2}|\d)#', (string) $time);
    }
}
