<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Tasks;

use League\Container\ServiceProvider\AbstractServiceProvider;

class TasksServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'queue',
        'tasks',
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $this->container->share('queue', function () {
            return new TasksQueue($this->container);
        });

        $this->container->share('tasks', function () {
            return new TasksHandler($this->container);
        });
    }
}
