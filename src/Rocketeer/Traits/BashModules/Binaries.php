<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Traits\BashModules;

/**
 * Handles finding and calling binaries.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait Binaries
{
    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// BINARIES ///////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get an AnonymousBinary instance.
     *
     * @param string $binary
     *
     * @return \Rocketeer\Abstracts\AbstractBinary|\Rocketeer\Abstracts\AbstractPackageManager
     */
    public function binary($binary)
    {
        return $this->builder->buildBinary($binary);
    }

    /**
     * Prefix a command with the right path to PHP.
     *
     * @return \Rocketeer\Binaries\Php
     */
    public function php()
    {
        return $this->binary('php');
    }

    /**
     * Prefix a command with the right path to Composer.
     *
     * @return \Rocketeer\Binaries\Composer
     */
    public function composer()
    {
        return $this->binary('composer');
    }

    /**
     * @return \Rocketeer\Binaries\Phpunit
     */
    public function phpunit()
    {
        return $this->binary('phpunit');
    }

    /**
     * @return \Rocketeer\Binaries\Artisan
     */
    public function artisan()
    {
        return $this->binary('artisan');
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// HELPERS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get the path to a binary.
     *
     * @param string      $binary   The name of the binary
     * @param string|null $fallback A fallback location
     * @param bool        $prompt
     *
     * @return string
     */
    public function which($binary, $fallback = null, $prompt = true)
    {
        $locations = [
            $this->localStorage->get($this->getBinaryStoragePath($binary)),
            $this->paths->getPath($binary),
            $binary,
        ];

        // Add fallback if provided
        if ($fallback) {
            $locations[] = $fallback;
        }

        // Add command prompt if possible
        if ($this->hasCommand() && $prompt) {
            $prompt      = $binary.' could not be found, please enter the path to it';
            $locations[] = [$this->command, 'ask', $prompt];
        }

        return $this->whichFrom($binary, $locations);
    }

    /**
     * Scan an array of locations for a binary.
     *
     * @param string $binary
     * @param array  $locations
     *
     * @return string
     */
    protected function whichFrom($binary, array $locations)
    {
        $location = false;

        // Look in all the locations
        $tryout = 0;
        while (!$location && array_key_exists($tryout, $locations)) {

            // Execute method if required
            $location = $locations[$tryout];
            if (is_array($location)) {
                list($object, $method, $argument) = $location;
                $location                         = $object->$method($argument);
            }

            // Verify existence of returned path
            if ($location && $location !== $this->paths->getPath($binary)) {
                $location = $this->rawWhich($location);
            }

            $tryout++;
        }

        // Store found location or remove it if invalid
        if (!$this->local) {
            if ($location) {
                $this->localStorage->set($this->getBinaryStoragePath($binary), $location);
            } else {
                $this->localStorage->forget($this->getBinaryStoragePath($binary));
            }
        }

        return $location ?: $binary;
    }

    /**
     * Do a straight call to which.
     *
     * @param string $location
     *
     * @return string|false
     */
    public function rawWhich($location)
    {
        $location = $this->bash->runSilently('which '.$location);
        if (strpos($location, 'not found') !== false || strpos($location, 'in (') !== false) {
            return false;
        }

        return $location;
    }

    /**
     * Check the Laravel version.
     *
     * @param string $version  The version to check against
     * @param string $operator The operator (default: '>=')
     *
     * @return bool
     */
    public function versionCheck($version, $operator = '>=')
    {
        $app = $this->app;
        if (is_a($app, 'Illuminate\Foundation\Application')) {
            return version_compare($app::VERSION, $version, $operator);
        }

        return false;
    }

    /**
     * Get the path in which to store/retrieve a binary's path.
     *
     * @param string $binary
     *
     * @return string
     */
    protected function getBinaryStoragePath($binary)
    {
        return 'paths.'.$this->connections->getConnection().'.'.$binary;
    }
}
