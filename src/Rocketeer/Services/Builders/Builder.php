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
use Rocketeer\Services\Modules\ModulableInterface;
use Rocketeer\Services\Modules\ModulableTrait;
use Rocketeer\Traits\ContainerAwareTrait;

/**
 * @mixin BinariesBuilder
 * @mixin CommandsBuilder
 * @mixin StrategiesBuilder
 * @mixin TasksBuilder
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
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
            'Rocketeer\Binaries\Scm\%s',
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

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// LOOKUPS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

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

        $this->lookups[$type] = array_merge($this->lookups[$type], $lookups);
    }

    /**
     * Add additional places to look for multiple types.
     *
     * @param array $lookups
     */
    public function registerLookups(array $lookups)
    {
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
        $paths = $this->getLookups($type);
        $paths[] = '%s';

        // Create classes array
        $class = ucfirst($class);
        $classes = $namespace ? [ucfirst($namespace).'\\'.$class, $class] : [$class];

        // Search for first existing class
        foreach ($classes as $class) {
            foreach ($paths as $path) {
                $path = sprintf($path, $class);
                if (class_exists($path)) {
                    return $path;
                }
            }
        }

        return false;
    }
}
