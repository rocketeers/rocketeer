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

namespace Rocketeer\Services\Ignition;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Rocketeer\Traits\HasLocatorTrait;

class IgnitionServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    use HasLocatorTrait;

    /**
     * @var array
     */
    protected $provides = [
        'igniter',
        'igniter.tasks',
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share('igniter', Configuration::class)->withArgument($this->container);
        $this->container->share('igniter.tasks', Tasks::class)->withArgument($this->container);
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->register();

        $this->igniter->bindPaths();
        $this->igniter->loadUserConfiguration();

        $tasksIgniter = $this->container->get('igniter.tasks');

        $tasks = $tasksIgniter->getPredefinedTasks();
        $tasksIgniter->registerTasksAndCommands($tasks);
    }
}
