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

use Fuel\Alias\Manager;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Rocketeer\Facades\Rocketeer;
use Rocketeer\Services\Ignition\Modules\ConfigurationBootstrapper;
use Rocketeer\Services\Ignition\Modules\PathsBootstrapper;
use Rocketeer\Services\Ignition\Modules\TasksBootstrapper;
use Rocketeer\Services\Ignition\Modules\UserBootstrapper;
use Rocketeer\Traits\HasLocatorTrait;

class IgnitionServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    use HasLocatorTrait;

    /**
     * @var array
     */
    protected $provides = [
        Bootstrapper::class,
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
        $this->container->share(Bootstrapper::class, function () {
            $bootstrapper = new Bootstrapper($this->container);
            $bootstrapper->register(new ConfigurationBootstrapper());
            $bootstrapper->register(new PathsBootstrapper());
            $bootstrapper->register(new TasksBootstrapper());
            $bootstrapper->register(new UserBootstrapper());

            return $bootstrapper;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->register();

        // Set container onto facade
        Rocketeer::setContainer($this->container);

        // Add facade alias
        $manager = new Manager();
        $manager->register();
        $manager->alias('Rocketeer', Rocketeer::class);

        // Bootstrap Rocketeer
        $this->bootstrapper->bootstrapPaths();
        $this->bootstrapper->bootstrapConfiguration();
        $this->bootstrapper->bootstrapTasks();
        $this->bootstrapper->bootstrapUserCode();
    }
}
