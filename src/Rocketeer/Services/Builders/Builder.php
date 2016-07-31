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

namespace Rocketeer\Services\Builders;

use League\Container\ContainerAwareInterface;
use Rocketeer\Services\Builders\Modules\BinariesBuilder;
use Rocketeer\Services\Builders\Modules\CommandsBuilder;
use Rocketeer\Services\Builders\Modules\StrategiesBuilder;
use Rocketeer\Services\Builders\Modules\TasksBuilder;
use Rocketeer\Services\Connections\Shell\Modules\Binaries;
use Rocketeer\Services\Connections\Shell\Modules\Core;
use Rocketeer\Services\Connections\Shell\Modules\Filesystem;
use Rocketeer\Services\Connections\Shell\Modules\Flow;
use Rocketeer\Services\Connections\Shell\Modules\Statuses;
use Rocketeer\Services\Container\Container;
use Rocketeer\Services\Modules\ModulableInterface;
use Rocketeer\Services\Modules\ModulableTrait;
use Rocketeer\Traits\ContainerAwareTrait;

/**
 * @mixin BinariesBuilder
 * @mixin CommandsBuilder
 * @mixin StrategiesBuilder
 * @mixin TasksBuilder
 */
class Builder implements ModulableInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ModulableTrait;

    /**
     * The possible locations of
     * the various types.
     *
     * @var array
     */
    protected $lookups = [
        'binaries' => [
            'Rocketeer\Binaries\PackageManagers\%s',
            'Rocketeer\Binaries\Vcs\%s',
            'Rocketeer\Binaries\%s',
        ],
        'tasks' => [
            'Rocketeer\Tasks\%s',
            'Rocketeer\Tasks\Subtasks\%s',
        ],
        'commands' => [
            'Rocketeer\Console\Commands\%sCommand',
            'Rocketeer\Console\Commands\BaseTaskCommand',
        ],
        'strategies' => [
            'Rocketeer\Strategies\%sStrategy',
        ],
    ];

    /**
     * @var array
     */
    protected $lookedUp = [];

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $namespace = $this->bootstrapper->getUserNamespace();

        $this->registerLookups([
            'tasks' => $namespace.'\Tasks\%s',
            'strategies' => $namespace.'\Strategies\%s',
        ]);
    }

    ////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////// MODULES ////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * @param ModulableInterface $modulable
     *
     * @return ModulableInterface
     */
    public function registerBashModulesOn(ModulableInterface $modulable)
    {
        if (!$modulable->getRegistered()) {
            $modulable->register(new Binaries());
            $modulable->register(new Core());
            $modulable->register(new Filesystem());
            $modulable->register(new Flow());
            $modulable->register(new Statuses());
        }

        return $modulable;
    }

    ////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////// LOOKUPS ////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Get the lookups for a type.
     *
     * @param string $type
     *
     * @return array
     */
    public function getLookups($type)
    {
        return (array) isset($this->lookups[$type]) ? $this->lookups[$type] : [];
    }

    /**
     * Add additional places to look for classes.
     *
     * @param string       $type
     * @param string|array $lookups
     */
    public function registerLookup($type, $lookups = [])
    {
        $lookups = (array) $lookups;

        $this->lookups[$type] = array_unique(array_merge($this->lookups[$type], $lookups));
    }

    /**
     * Add additional places to look for multiple types.
     *
     * @param array $lookups
     */
    public function registerLookups(array $lookups)
    {
        $this->lookedUp = [];
        foreach ($lookups as $type => $lookup) {
            $this->registerLookup($type, $lookup);
        }
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Find a class in various predefined namespaces.
     *
     * @param string      $class
     * @param string      $type
     * @param string|null $namespace
     *
     * @return false|string
     */
    public function findQualifiedName($class, $type, $namespace = null)
    {
        // Check if we already resolved that name
        if (array_key_exists($class, $this->lookedUp)) {
            return $this->lookedUp[$type][$class];
        }

        $paths = $this->getLookups($type);
        $paths[] = '%s';

        // Create classes array
        $class = ucfirst($class);
        $classes = $namespace ? [ucfirst($namespace).'\\'.$class, $class] : [$class];

        // Search for first existing class
        $qualified = false;
        foreach ($classes as $class) {
            foreach ($paths as $path) {
                $path = sprintf($path, $class);
                if (class_exists($path)) {
                    $qualified = $path;
                    break 2;
                }
            }
        }

        return $this->lookedUp[$type][$class] = $qualified;
    }
}
