<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Builders;

use Illuminate\Support\Arr;
use Rocketeer\Traits\HasLocator;

/**
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Builder
{
    use HasLocator;

    use TasksBuilder;
    use StrategiesBuilder;
    use BinariesBuilder;
    use CommandsBuilder;

    /**
     * The possible locations of
     * the various types.
     *
     * @type array
     */
    protected $lookups = array(
        'binaries'   => array(
            'Rocketeer\Binaries\PackageManagers\%s',
            'Rocketeer\Binaries\Scm\%s',
            'Rocketeer\Binaries\%s',
        ),
        'tasks'      => array(
            'Rocketeer\Tasks\%s',
            'Rocketeer\Tasks\Subtasks\%s',
        ),
        'commands'   => array(
            'Rocketeer\Console\Commands\%sCommand',
            'Rocketeer\Console\Commands\BaseTaskCommand',
        ),
        'strategies' => array(
            'Rocketeer\Strategies\%sStrategy',
        ),
    );

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
        return (array) Arr::get($this->lookups, $type);
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
    protected function findQualifiedName($class, $type, $namespace = null)
    {
        $paths   = $this->builder->getLookups($type);
        $paths[] = '%s';

        // Create classes array
        $class   = ucfirst($class);
        $classes = [$class];
        if ($namespace) {
            $classes = array(
                ucfirst($namespace).'\\'.$class,
                $class,
            );
        }

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
