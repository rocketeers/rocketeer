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

namespace Rocketeer\Facades;

use League\Container\ContainerInterface;
use Rocketeer\Services\Container\Container;
use Rocketeer\Services\Tasks\TasksHandler;

/**
 * Facade for Rocketeer's CLI.
 *
 * @mixin TasksHandler
 */
class Rocketeer
{
    /**
     * @var ContainerInterface
     */
    protected static $container;

    /**
     * @param ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container)
    {
        static::$container = $container;
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        if (!static::$container) {
            static::$container = new Container();
        }

        return static::$container->get(TasksHandler::class)->$name(...$arguments);
    }
}
