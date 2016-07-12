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
use Rocketeer\Tasks\Check;
use Rocketeer\Tasks\Cleanup;
use Rocketeer\Tasks\CurrentRelease;
use Rocketeer\Tasks\Dependencies;
use Rocketeer\Tasks\Deploy;
use Rocketeer\Tasks\Ignite;
use Rocketeer\Tasks\Migrate;
use Rocketeer\Tasks\Plugins\Installer;
use Rocketeer\Tasks\Plugins\Updater;
use Rocketeer\Tasks\Rollback;
use Rocketeer\Tasks\Setup;
use Rocketeer\Tasks\Subtasks\CreateRelease;
use Rocketeer\Tasks\Subtasks\FridayDeploy;
use Rocketeer\Tasks\Subtasks\Notify;
use Rocketeer\Tasks\Subtasks\Primer;
use Rocketeer\Tasks\Subtasks\SwapSymlink;
use Rocketeer\Tasks\Teardown;
use Rocketeer\Tasks\Test;
use Rocketeer\Tasks\Update;
use Rocketeer\Traits\HasLocatorTrait;

class IgnitionServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    use HasLocatorTrait;

    /**
     * @var array
     */
    protected $provides = [
        'igniter',
        'rocketeer.tasks.check',
        'rocketeer.tasks.cleanup',
        'rocketeer.tasks.create-release',
        'rocketeer.tasks.current',
        'rocketeer.tasks.dependencies',
        'rocketeer.tasks.deploy',
        'rocketeer.tasks.friday-deploy',
        'rocketeer.tasks.ignite',
        'rocketeer.tasks.installer',
        'rocketeer.tasks.migrate',
        'rocketeer.tasks.notify',
        'rocketeer.tasks.primer',
        'rocketeer.tasks.rollback',
        'rocketeer.tasks.setup',
        'rocketeer.tasks.swap-symlink',
        'rocketeer.tasks.teardown',
        'rocketeer.tasks.test',
        'rocketeer.tasks.update',
        'rocketeer.tasks.updater',
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share('igniter', Configuration::class)->withArgument($this->container);
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->register();

        $this->igniter->bindPaths();
        $this->igniter->loadUserConfiguration();

        $this->registerTasks();
    }

    /**
     * Register the existing tasks onto
     * the internal container.
     */
    public function registerTasks()
    {
        $tasks = [
            Check::class,
            Cleanup::class,
            CreateRelease::class,
            CurrentRelease::class,
            Dependencies::class,
            Deploy::class,
            FridayDeploy::class,
            Ignite::class,
            Installer::class,
            Migrate::class,
            Notify::class,
            Primer::class,
            Rollback::class,
            Setup::class,
            SwapSymlink::class,
            Teardown::class,
            Test::class,
            Update::class,
            Updater::class,
        ];

        foreach ($tasks as $task) {
            $task = $this->builder->buildTask($task);
            $this->container->add('rocketeer.tasks.'.$task->getSlug(), $task);
        }
    }
}
