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

namespace Rocketeer\Services\Environment;

use InvalidArgumentException;
use Rocketeer\Services\Environment\Pathfinders\AbstractPathfinder;
use Rocketeer\Services\Environment\Pathfinders\LocalPathfinder;
use Rocketeer\Services\Environment\Pathfinders\PathfinderInterface;
use Rocketeer\Services\Environment\Pathfinders\ServerPathfinder;

/**
 * Locates folders and paths on the server and locally.
 *
 * @mixin LocalPathfinder
 * @mixin ServerPathfinder
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Pathfinder extends AbstractPathfinder
{
    /**
     * @var array
     */
    protected $lookups = [];

    /**
     * @var array
     */
    protected $pathfinders = [];

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// PROVIDERS /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Register a paths provider with the Pathfinder.
     *
     * @param string|PathfinderInterface $pathfinder
     */
    public function registerPathfinder($pathfinder)
    {
        // Build pathfinder if necessary
        if (is_string($pathfinder)) {
            $pathfinder = $this->container->get($pathfinder);
        }

        // Check interfaces
        if (!$pathfinder instanceof PathfinderInterface) {
            throw new InvalidArgumentException('Pathfinder must implement PathfinderInterface');
        }

        // Register provided methods
        $provided = $pathfinder->provides();
        $classname = get_class($pathfinder);
        foreach ($provided as $method) {
            $this->lookups[$method] = $classname;
        }

        // Cache Pathfinder instance
        $this->pathfinders[$classname] = $pathfinder;
    }

    /**
     * Delegate calls to subpathfinders.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (array_key_exists($method, $this->lookups)) {
            $pathfinder = $this->lookups[$method];
            $pathfinder = $this->pathfinders[$pathfinder];

            return $pathfinder->$method(...$arguments);
        }
    }

    /**
     * The methods this pathfinder provides.
     *
     * @return string[]
     */
    public function provides()
    {
        return [];
    }
}
