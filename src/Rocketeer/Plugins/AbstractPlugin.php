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
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Rocketeer\Console\Console;
use Rocketeer\Services\Builders\Builder;
use Rocketeer\Services\Tasks\TasksHandler;
use Rocketeer\Traits\HasLocatorTrait;

/**
 * A basic abstract class for Rocketeer plugins to extend.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class AbstractPlugin extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    use HasLocatorTrait;

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
     * {@inheritdoc}
     */
    public function register()
    {
        // ...
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->register();

        $this->onBuilder($this->builder);
        $this->onConsole($this->console);
        $this->onQueue($this->tasks);
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
