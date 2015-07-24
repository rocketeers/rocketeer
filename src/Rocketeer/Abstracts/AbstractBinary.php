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
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Rocketeer\Traits\HasLocator;

/**
 * A generic class to represent a binary as a class.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class AbstractBinary
{
    use HasLocator;

    /**
     * The core binary.
     *
     * @type string
     */
    protected $binary;

    /**
     * A parent binary to call this one with.
     *
     * @type AbstractBinary|string
     */
    protected $parent;

    /**
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;

        // Assign default paths
        $paths = $this->getKnownPaths();
        if ($this->connections->getConnection() && $paths) {
            $binary   = Arr::get($paths, 0);
            $fallback = Arr::get($paths, 1);
            $binary   = $this->bash->which($binary, $fallback, false);

            $this->setBinary($binary);
        } elseif ($paths) {
            $this->setBinary($paths[0]);
        }
    }

    /**
     * Get an array of default paths to look for.
     *
     * @return array
     */
    protected function getKnownPaths()
    {
        return [];
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// PROPERTIES /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param AbstractBinary|string $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @param string $binary
     */
    public function setBinary($binary)
    {
        $this->binary = $binary;
    }

    /**
     * Get the current binary name.
     *
     * @return string
     */
    public function getBinary()
    {
        return $this->binary;
    }

    /**
     * Call or execute a command on the Binary.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return string|null
     */
    public function __call($name, $arguments)
    {
        // Execution aliases
        if (Str::startsWith($name, 'run')) {
            $command = array_shift($arguments);
            $command = call_user_func_array([$this, $command], $arguments);

            return $this->bash->$name($command);
        }

        // Format name
        $name = Str::snake($name, '-');

        // Prepend command name to arguments and call
        array_unshift($arguments, $name);
        $command = call_user_func_array([$this, 'getCommand'], $arguments);

        return $command;
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// HELPERS ///////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Returns a command with the SCM's binary.
     *
     * @param string|null     $command
     * @param string|string[] $arguments
     * @param string|string[] $flags
     *
     * @return string
     */
    public function getCommand($command = null, $arguments = [], $flags = [])
    {
        // Format arguments
        $arguments = $this->buildArguments($arguments);
        $options   = $this->buildOptions($flags);

        // Build command
        $binary     = $this->binary;
        $components = [$command, $arguments, $options];
        foreach ($components as $component) {
            if ($component) {
                $binary .= ' '.$component;
            }
        }

        // If the binary has a parent, wrap the call with it
        $parent  = $this->parent instanceof AbstractBinary ? $this->parent->getBinary() : $this->parent;
        $command = $parent.' '.$binary;

        return trim($command);
    }

    /**
     * @param string|string[] $flags
     *
     * @return string
     */
    protected function buildOptions($flags)
    {
        // Return if already builts
        if (is_string($flags)) {
            return $flags;
        }

        $options = [];
        $flags   = (array) $flags;

        // Flip array if necessary
        $firstKey = Arr::get(array_keys($flags), 0);
        if ($firstKey !== null && is_int($firstKey)) {
            $flags = array_combine(
                array_values($flags),
                array_fill(0, count($flags), null)
            );
        }

        // Build flags
        foreach ($flags as $flag => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    $options[] = $flag.'="'.$v.'"';
                }
            } else {
                if (is_numeric($flag)) {
                    $flag  = $value;
                    $value = null;
                }
                $options[] = $value ? $flag.'="'.$value.'"' : $flag;
            }
        }

        return implode(' ', $options);
    }

    /**
     * @param string|string[] $arguments
     *
     * @return string
     */
    protected function buildArguments($arguments)
    {
        if (!is_string($arguments)) {
            $arguments = (array) $arguments;
            $arguments = implode(' ', $arguments);
        }

        return $arguments;
    }

    /**
     * Quote a string.
     *
     * @param string $string
     *
     * @return string
     */
    protected function quote($string)
    {
        return '"'.$string.'"';
    }
}
