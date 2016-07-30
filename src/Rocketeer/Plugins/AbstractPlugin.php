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

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Rocketeer\Console\Console;
use Rocketeer\Services\Builders\Builder;
use Rocketeer\Services\Tasks\TasksHandler;
use Rocketeer\Traits\HasLocatorTrait;

/**
 * A basic abstract class for Rocketeer plugins to extend.
 */
abstract class AbstractPlugin extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    use HasLocatorTrait;

    /**
     * Additional lookups to add to Rocketeer.
     *
     * @var array
     */
    protected $lookups = [];

    /**
     * @var string
     */
    protected $name;

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
        $this->builder->registerLookups($this->lookups);

        $this->onBuilder($this->builder);
        $this->onConsole($this->console);
        $this->onQueue($this->tasks);
    }

    ////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////// HOOKS /////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

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
     * @param TasksHandler $tasks
     */
    public function onQueue(TasksHandler $tasks)
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
        // ...
    }

    ////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////// HELPERS ////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * @param string|null $option
     *
     * @return mixed
     */
    protected function getPluginOption($option = null)
    {
        return $this->config->getPluginOption($this->name, $option);
    }
}
