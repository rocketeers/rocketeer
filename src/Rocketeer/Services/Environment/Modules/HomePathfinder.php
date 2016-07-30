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

/**
 * Get paths related to the user's home folder.
 */
class HomePathfinder extends AbstractPathfinderModule
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
     * @return string[]
     */
    public function getProvided()
    {
        return [
            'getUserHomeFolder',
            'getDefaultKeyPath',
            'getRocketeerConfigFolder',
            'getConfigurationCachePath',
        ];
    }
}
