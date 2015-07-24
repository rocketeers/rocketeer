<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Abstracts;

use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Rocketeer\Services\TasksHandler;
use Rocketeer\Traits\HasLocator;

/**
 * A basic abstract class for Rocketeer plugins to extend.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class AbstractPlugin
{
    use HasLocator;

    /**
     * The path to the configuration folder.
     *
     * @type string
     */
    public $configurationFolder;

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
     * @param Container $app
     *
     * @return Container
     */
    public function register(Container $app)
    {
        return $app;
    }

    /**
     * Register Tasks with Rocketeer.
     *
     * @param \Rocketeer\Services\TasksHandler $queue
     */
    abstract public function onQueue(TasksHandler $queue);
}
