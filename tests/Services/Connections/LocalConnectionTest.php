<?php
namespace Rocketeer\Services\Connections;

use Rocketeer\TestCases\RocketeerTestCase;

class LocalConnectionTest extends RocketeerTestCase
{
    public function testCanGetPreviousStatus()
    {
        $task = $this->task;
        $task->setLocal(true);
        $task->run('ls');

        $this->assertTrue($task->status());
    }

    public function testCanExecuteCommandInDirectory()
    {
        $task = $this->task;
        $task->setLocal(true);
        $results = $task->runForCurrentRelease('pwd');

        $this->assertEquals(realpath($this->server.'/releases/20000000000000'), $results);
    }

    public function testCanDisplayCorrectCommandHandle()
    {
        $this->mockCommand(['verbose' => true]);
        $this->rocketeer->setLocal(true);

        $this->task->getConnection()->display('foobar');

        $this->assertEquals(['[anahkiasen@local] foobar'], $this->logs->getLogs());
    }
}
