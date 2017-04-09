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

namespace Rocketeer\Binaries;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Rocketeer\Services\Connections\Shell\Bash;
use Rocketeer\Traits\ContainerAwareTrait;

/**
 * A generic class to represent a binary as a class.
 *
 * @mixin Bash
 */
abstract class AbstractBinary
{
    use ContainerAwareTrait;

    /**
     * The core binary.
     *
     * @var string
     */
    protected $binary;

    /**
     * @var bool
     */
    protected $resolved = false;

    /**
     * A parent binary to call this one with.
     *
     * @var AbstractBinary|string
     */
    protected $parent;

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
        $this->resolved = true;
    }

    /**
     * Get the name of the binary class.
     *
     * @return string
     */
    public function getName()
    {
        return class_basename($this);
    }

    /**
     * Get the current binary name.
     *
     * @return string
     */
    public function getBinary()
    {
        $default = $this->binary;

        // Resolve true path to binary
        if (!$this->resolved) {
            $paths = $this->getKnownPaths() ?: [$default];
            if ($this->connections->getCurrentConnectionKey() && $paths) {
                $binary = Arr::get($paths, 0);
                $fallback = Arr::get($paths, 1);
                $this->setBinary($this->bash->which($binary, $fallback, false));
            } elseif ($paths) {
                $this->setBinary($paths[0]);
            }
        }

        return $this->binary ?: $default;
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
            $command = $this->$command(...$arguments);

            return $this->bash->$name($command);
        }

        // Format name
        $name = Str::snake($name, '-');

        // Prepend command name to arguments and call
        array_unshift($arguments, $name);
        $command = $this->getCommand(...$arguments);

        return $command;
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// HELPERS ///////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Returns a command with the VCS's binary.
     *
     * @param string|null     $command
     * @param string|string[] $arguments
     * @param string|string[] $flags
     * @param array           $environmentVariables
     *
     * @return string
     */
    public function getCommand($command = null, $arguments = [], $flags = [], $environmentVariables = [])
    {
        // Format arguments
        $arguments = $this->buildArguments($arguments);
        $options = $this->buildOptions($flags);
        $environmentVariables = $this->buildEnvironmentVariables($environmentVariables);

        // Build command
        $binary = $this->getBinary();
        $components = [$command, $arguments, $options];
        foreach ($components as $component) {
            if ($component) {
                $binary .= ' '.$component;
            }
        }

        // If the binary has a parent, wrap the call with it
        $parent = $this->parent instanceof self ? $this->parent->getBinary() : $this->parent;
        $command = $environmentVariables.$parent.' '.$binary;

        return trim($command);
    }

    /**
     * @param string|string[] $flags
     *
     * @return string
     */
    protected function buildOptions($flags)
    {
        // Return if already built
        if (is_string($flags)) {
            return $flags;
        }

        // Build flags
        $options = [];
        $flags = $flags ? $this->sanitizeFlags($flags) : [];
        foreach ($flags as $flag => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    $options[] = $flag.'="'.$v.'"';
                }
            } else {
                if (is_numeric($flag)) {
                    $flag = $value;
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
     * @param array $env
     *
     * @return string
     */
    protected function buildEnvironmentVariables($env)
    {
        $variables = '';
        foreach ($env as $key => $value) {
            $variables .= $key.'='.$value.' ';
        }

        return trim($variables);
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

    /**
     * Sanitize a flags array.
     *
     * @param array $flags
     *
     * @return array
     */
    protected function sanitizeFlags(array $flags)
    {
        $flags = (array) $flags;

        // Flip array if necessary
        $firstKey = Arr::get(array_keys($flags), 0);
        if ($firstKey !== null && is_int($firstKey)) {
            $flags = array_combine(
                array_values($flags),
                array_fill(0, count($flags), null)
            );
        }

        return $flags;
    }
}
