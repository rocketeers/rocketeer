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

namespace Rocketeer\Services\Bootstrapper;

use ErrorException;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Rocketeer\Facades\Rocketeer;
use Rocketeer\Services\Bootstrapper\Modules\ConfigurationBootstrapper;
use Rocketeer\Services\Bootstrapper\Modules\PathsBootstrapper;
use Rocketeer\Services\Bootstrapper\Modules\TasksBootstrapper;
use Rocketeer\Services\Bootstrapper\Modules\UserBootstrapper;
use Rocketeer\Traits\HasLocatorTrait;

class BootstrapperServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
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

        // Handle errors as exceptions
        set_error_handler(function ($level, $message, $file, $line) {
            if (error_reporting() & $level) {
                throw new ErrorException($message, 0, $level, $file, $line);
            }
        });

        // Set container onto facade and setup alias
        Rocketeer::setContainer($this->container);
        if (!class_exists('Rocketeer')) {
            class_alias(Rocketeer::class, 'Rocketeer');
        }

        // Bootstrap Rocketeer
        $this->bootstrapper->bootstrapPaths();
        $this->bootstrapper->bootstrapTasks();
        $this->bootstrapper->bootstrapConfiguration();

        // Load the user's events, tasks, plugins, and configurations
        $this->bootstrapper->bootstrapUserFiles();
    }
}
