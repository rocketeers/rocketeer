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

namespace Rocketeer\Services\Roles;

use Rocketeer\Services\Connections\ConnectionsFactory;
use Rocketeer\TestCases\RocketeerTestCase;

class RolesManagerTest extends RocketeerTestCase
{
    public function testCanGetRolesOfConnection()
    {
        $roles = ['assets', 'web'];
        $this->swapConnections([
            'production' => [
                'roles' => $roles,
            ],
        ]);

        $this->assertEquals($roles, $this->roles->getConnectionRoles('production'));
    }

    public function testCanCheckIfConnectionCanExecuteTask()
    {
        $remote = new ConnectionsFactory($this->container);
        $key = $this->credentials->createConnectionKey('production');
        $connection = $remote->make($key);
        $connection->setRoles(['foo', 'bar']);

        $this->task->setRoles(['foo']);
        $this->assertTrue($this->roles->canExecuteTask($connection, $this->task));

        $this->task->setRoles(['foo', 'baz']);
        $this->assertFalse($this->roles->canExecuteTask($connection, $this->task));
    }

    public function testCanAssignRolesToTask()
    {
        $this->roles->assignTasksRoles([
            'web' => ['Deploy', 'Check'],
        ]);

        $roles = $this->task('Deploy')->getRoles();
        $this->assertEquals(['web'], $roles);

        $roles = $this->task('Check')->getRoles();
        $this->assertEquals(['web'], $roles);
    }

    public function testCanAssignRolesFromConfiguration()
    {
        $this->swapConfig([
            'hooks.roles' => [
                'web' => 'Deploy',
                'assets' => 'Deploy',
            ],
        ]);

        $this->assertEquals(['web', 'assets'], $this->task('Deploy')->getRoles());
    }

    public function testTasksWithoutRolesAreCompatibleWithAnyServer()
    {
        $this->container->add(ConnectionsFactory::class, new ConnectionsFactory());
        $this->swapConnections([
            'production' => [
                'host' => 'foobar.com',
                'username' => 'foobar',
                'password' => 'foobar',
                'roles' => ['web', 'assets'],
            ],
        ]);

        $compatible = $this->roles->canExecuteTask($this->connections->getCurrentConnection(), $this->task('Deploy'));
        $this->assertTrue($compatible);
    }
}
