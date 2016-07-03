<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Facades;

use Rocketeer\Container;

/**
 * Facade for Rocketeer's CLI.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class StandaloneFacade
{
    /**
     * @var Container
     */
    protected static $container;

    /**
     * The class to fetch from the container.
     *
     * @var string
     */
    protected static $accessor;

    /**
     * The resolved object instances.
     *
     * @var array
     */
    protected static $resolvedInstance;

    /**
     * @param Container $container
     */
    public static function setContainer(Container $container)
    {
        static::$container = $container;
    }

    /**
     * @return Container
     */
    public static function getContainer()
    {
        return self::$container;
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        if (!static::$container) {
            static::$container = new Container();
        }

        return static::$accessor;
    }

    /**
     * @return object
     */
    protected static function getInstance()
    {
        $name = static::getFacadeAccessor();
        if (is_object($name)) {
            return $name;
        }

        if (isset(static::$resolvedInstance[$name])) {
            return static::$resolvedInstance[$name];
        }

        return static::$resolvedInstance[$name] = static::$container->get($name);
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::getInstance()->$name(...$arguments);
    }
}
