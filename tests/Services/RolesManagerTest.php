<?php
namespace Rocketeer\Services;

use Rocketeer\Services\Connections\RemoteHandler;
use Rocketeer\TestCases\RocketeerTestCase;

class RolesManagerTest extends RocketeerTestCase
{
    public function testCanGetRolesOfConnection()
    {
        $roles = ['assets', 'web'];
        $this->swapConfig(array(
           'rocketeer::connections' => array(
             'production' => array(
               'roles' => $roles,
             ),
           ),
        ));

        $this->assertEquals($roles, $this->roles->getRoles('production'));
    }

    public function testCanCheckIfConnectionCanExecuteTask()
    {
        $remote = new RemoteHandler($this->app);
        $connection = $remote->connection('production');
        $connection->setRoles(['foo', 'bar']);

        $this->task->setRoles(['foo']);
        $this->assertTrue($this->roles->canExecuteTask($connection, $this->task));

        $this->task->setRoles(['foo', 'baz']);
        $this->assertFalse($this->roles->canExecuteTask($connection, $this->task));
    }
}
