<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\Environment;

use InvalidArgumentException;
use Rocketeer\Services\Environment\Pathfinders\AbstractPathfinder;
use Rocketeer\Services\Environment\Pathfinders\PathfinderInterface;

/**
 * Locates folders and paths on the server and locally.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Pathfinder extends AbstractPathfinder
{
    /**
     * @type array
     */
    protected $lookups = [];

    /**
     * @type array
     */
    protected $pathfinders = [];

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// PROVIDERS /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Register a paths provider with the Pathfinder
     *
     * @param string|PathfinderInterface $pathfinder
     */
    public function registerPathfinder($pathfinder)
    {
        // Build pathfinder if necessary
        if (is_string($pathfinder)) {
            $pathfinder = $this->app->make($pathfinder);
        }

        // Check interfaces
        if (!$pathfinder instanceof PathfinderInterface) {
            throw new InvalidArgumentException('Pathfinder must implement PathfinderInterface');
        }

        // Register provided methods
        $provided  = $pathfinder->provides();
        $classname = get_class($pathfinder);
        foreach ($provided as $method) {
            $this->lookups[$method] = $classname;
        }

        // Cache Pathfinder instance
        $this->pathfinders[$classname] = $pathfinder;
    }

    /**
     * Delegate calls to subpathfinders
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

            return call_user_func_array([$pathfinder, $method], $arguments);
        }
    }

    /**
     * The methods this pathfinder provides
     *
     * @return string[]
     */
    public function provides()
    {
        return [];
    }
}
