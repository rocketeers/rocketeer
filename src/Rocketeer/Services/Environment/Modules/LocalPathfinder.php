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

namespace Rocketeer\Services\Environment\Modules;

use Exception;
use Illuminate\Support\Str;

class LocalPathfinder extends AbstractPathfinderModule
{
    /**
     * Get the path to the users home folder.
     *
     * @throws Exception
     *
     * @return string
     */
    public static function getUserHomeFolder()
    {
        // Get home folder if available (Unix)
        if (!empty($_SERVER['HOME'])) {
            return $_SERVER['HOME'];
            // Else use the home drive (Windows)
        } elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
            return $_SERVER['HOMEDRIVE'].$_SERVER['HOMEPATH'];
        }
        throw new Exception('Cannot determine user home directory.');
    }

    /**
     * Get the default path for the SSH key.
     *
     * @throws Exception
     *
     * @return string
     */
    public function getDefaultKeyPath()
    {
        return $this->getUserHomeFolder().'/.ssh/id_rsa';
    }

    /**
     * Get the path to the Rocketeer config folder in the users home.
     *
     * @return string
     */
    public function getRocketeerConfigFolder()
    {
        return $this->getUserHomeFolder().'/.rocketeer';
    }

    /**
     * Get the path to the configuration cache.
     *
     * @return string
     */
    public function getConfigurationCachePath()
    {
        return $this->getRocketeerConfigFolder().'/caches/'.Str::slug(getcwd());
    }

    /**
     * Get the path to the application.
     *
     * @return string
     */
    public function getApplicationPath()
    {
        $applicationPath = $this->modulable->getPath('app').'/' ?: $this->modulable->getBasePath();
        $applicationPath = $this->modulable->unifySlashes($applicationPath);

        return $applicationPath;
    }

    /**
     * Get the path to the configuration folder.
     *
     * @return string
     */
    public function getConfigurationPath()
    {
        // Get path to configuration
        $configuration = $this->container->get('path.rocketeer.config');

        return $this->modulable->unifyLocalSlashes($configuration);
    }

    /**
     * @return string
     */
    public function getDotenvPath()
    {
        $path = $this->modulable->getBasePath().'.env';

        return $this->modulable->unifyLocalSlashes($path);
    }

    /**
     * Get path to the storage folder.
     *
     * @return string
     */
    public function getStoragePath()
    {
        // If no path is bound, default to the Rocketeer folder
        if (!$this->container->has('path.storage')) {
            return '.rocketeer';
        }

        // Unify slashes
        $storage = $this->container->get('path.storage');
        $storage = $this->modulable->unifySlashes($storage);
        $storage = str_replace($this->modulable->getBasePath(), null, $storage);

        return $storage;
    }

    /**
     * @return string[]
     */
    public function getProvided()
    {
        return [
            'getApplicationPath',
            'getConfigurationCachePath',
            'getConfigurationPath',
            'getDefaultKeyPath',
            'getDotenvPath',
            'getRocketeerConfigFolder',
            'getStoragePath',
            'getUserHomeFolder',
        ];
    }
}
