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

namespace Rocketeer\Services\Connections\Shell\Modules;

/**
 * Handles finding and calling binaries.
 *
 * @method \Rocketeer\Binaries\PackageManagers\Bower bower()
 * @method \Rocketeer\Binaries\PackageManagers\Bundler bundler()
 * @method \Rocketeer\Binaries\PackageManagers\Composer composer()
 * @method \Rocketeer\Binaries\PackageManagers\Npm npm()
 * @method \Rocketeer\Binaries\Php php()
 * @method \Rocketeer\Binaries\Phpunit phpunit()
 * @method \Rocketeer\Binaries\Vcs\Git git()
 * @method \Rocketeer\Binaries\Vcs\Hg hg()
 * @method \Rocketeer\Binaries\Vcs\Svn svn()
 */
class Binaries extends AbstractBashModule
{
    /**
     * @var bool
     */
    protected $default = true;

    /**
     * Get a binary.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return \Rocketeer\Binaries\PackageManagers\AbstractPackageManager|string
     */
    public function __call($name, $arguments)
    {
        $binary = $this->binary($name);
        if ($arguments) {
            return $binary->run(...$arguments);
        }

        return $binary;
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// BINARIES ///////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get an AnonymousBinary instance.
     *
     * @param string $binary
     *
     * @return \Rocketeer\Binaries\PackageManagers\AbstractPackageManager
     */
    public function binary($binary)
    {
        return $this->modulable->builder->buildBinary($binary);
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
            $this->paths->getPath($binary),
            $this->localStorage->get($this->getBinaryStoragePath($binary)),
            $binary,
        ];

        // Add fallback if provided
        if ($fallback) {
            $locations[] = $fallback;
        }

        // Add command prompt if possible
        if ($this->hasCommand() && $prompt) {
            $prompt = 'Binary "'.$binary.'" could not be found, please enter the path to it';
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
                $location = $object->$method($argument);
            }

            // Verify existence of returned path
            if ($location && $location !== $this->paths->getPath($binary)) {
                $location = $this->rawWhich($location);
            }

            ++$tryout;
        }

        // Store found location or remove it if invalid
        if (!$this->modulable->connections->is('local')) {
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
        $location = $this->modulable->runSilently('which '.$location);
        if (mb_strpos($location, 'not found') !== false || mb_strpos($location, 'in (') !== false) {
            return false;
        }

        return $location;
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
        return 'paths.'.$this->connections->getCurrentConnectionKey()->name.'.'.$binary;
    }

    /**
     * @return string[]
     */
    public function getProvided()
    {
        return [
            'binary',
            'rawWhich',
            'which',
        ];
    }
}
