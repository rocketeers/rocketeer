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

namespace Rocketeer\Plugins;

use Illuminate\Support\Str;
use Rocketeer\Console\Console;
use Rocketeer\Container;
use Rocketeer\Services\Builders\Builder;
use Rocketeer\Services\Tasks\TasksHandler;
use Rocketeer\Traits\ContainerAwareTrait;

/**
 * A basic abstract class for Rocketeer plugins to extend.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class AbstractPlugin
{
    use ContainerAwareTrait;

    /**
     * The path to the configuration folder.
     *
     * @var string
     */
    public $configurationFolder;

    /**
     * Additional lookups to
     * add to Rocketeer.
     *
     * @var array
     */
    protected $lookups = [];

    /**
     * Get the package namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        $namespace = str_replace('\\', '/', get_class($this));
        $namespace = Str::snake(basename($namespace));
        $namespace = str_replace('_', '-', $namespace);

        return $namespace;
    }

    /**
     * Bind additional classes to the Container.
     *
     * @param Container $container
     *
     * @return Container
     */
    public function register(Container $container)
    {
        return $container;
    }

    /**
     * Register additional commands.
     *
     * @param Console $console
     */
    public function onConsole(Console $console)
    {
        // ...
    }

    /**
     * Register Tasks with Rocketeer.
     *
     * @param TasksHandler $queue
     */
    public function onQueue(TasksHandler $queue)
    {
        // ...
    }

    /**
     * Register additional places to build from.
     *
     * @param Builder $builder
     */
    public function onBuilder(Builder $builder)
    {
        $builder->registerLookups($this->lookups);
    }
}
