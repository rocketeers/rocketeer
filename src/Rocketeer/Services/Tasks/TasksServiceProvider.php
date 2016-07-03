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
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share('queue', TasksQueue::class)->withArgument($this->container);
        $this->container->share('tasks', TasksHandler::class)->withArgument($this->container);
    }
}
