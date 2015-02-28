<?php
namespace Rocketeer\Services;

use Rocketeer\Services\Connections\RemoteHandler;
use Rocketeer\TestCases\RocketeerTestCase;

class RolesManagerTest extends RocketeerTestCase
{
    public function testCanGetRolesOfConnection()
    {
        $roles = ['assets', 'web'];
        $this->swapConnections(array(
            'production' => array(
                'roles' => $roles,
            ),
        ));

        $this->assertEquals($roles, $this->roles->getConnectionRoles('production'));
    }

    public function testCanCheckIfConnectionCanExecuteTask()
    {
        $remote     = new RemoteHandler($this->app);
        $connection = $remote->connection('production');
        $connection->setRoles(['foo', 'bar']);

        $this->task->setRoles(['foo']);
        $this->assertTrue($this->roles->canExecuteTask($connection, $this->task));

        $this->task->setRoles(['foo', 'baz']);
        $this->assertFalse($this->roles->canExecuteTask($connection, $this->task));
    }

    public function testCanAssignRolesToTask()
    {
        $this->roles->assignTasksRoles(array(
            'web' => ['Deploy', 'Check'],
        ));

        $roles = $this->task('Deploy')->getRoles();
        $this->assertEquals(['web'], $roles);

        $roles = $this->task('Check')->getRoles();
        $this->assertEquals(['web'], $roles);
    }

    public function testCanAssignRolesFromConfiguration()
    {
        $this->swapConfig(array(
            'hooks.roles' => array(
                'web'    => 'Deploy',
                'assets' => 'Deploy',
            ),
        ));

        $this->assertEquals(['web', 'assets'], $this->task('Deploy')->getRoles());
    }

    public function testTasksWithoutRolesAreCompatibleWithAnyServer()
    {
        $this->app['rocketeer.remote'] = new RemoteHandler($this->app);
        $this->swapConnections(array(
            'production' => array(
                'host'     => 'foobar.com',
                'username' => 'foobar',
                'password' => 'foobar',
                'roles'    => ['web', 'assets'],
            ),
        ));

        $compatible = $this->roles->canExecuteTask($this->remote->connection(), $this->task('Deploy'));
        $this->assertTrue($compatible);
    }
}
