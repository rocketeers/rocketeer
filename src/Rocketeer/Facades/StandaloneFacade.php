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

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Rocketeer\RocketeerServiceProvider;

/**
 * Facade for Rocketeer's CLI.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 *
 * @see    Rocketeer\Console\Console
 */
abstract class StandaloneFacade extends Facade
{
    /**
     * The class to fetch from the container.
     *
     * @type string
     */
    protected static $accessor;

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        if (!static::$app) {
            $container = new Container();
            $provider  = new RocketeerServiceProvider($container);
            $provider->boot();

            static::$app = $container;
        }

        return static::$accessor;
    }
}
