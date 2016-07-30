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

/**
 * Get paths related to the user's current application.
 */
class ApplicationPathfinder extends AbstractPathfinderModule
{
    /**
     * Get the path to the .rocketeer folder.
     *
     * @return string
     */
    public function getRocketeerPath()
    {
        // Get path to configuration
        $configuration = $this->modulable->getBasePath().'.rocketeer';

        return $this->modulable->unifyLocalSlashes($configuration);
    }

    /**
     * Get the path to the configuration folder.
     *
     * @return string
     */
    public function getConfigurationPath()
    {
        return $this->modulable->unifyLocalSlashes($this->getRocketeerPath().'/config');
    }

    /**
     * @return string
     */
    public function getLogsPath()
    {
        $folder = $this->config->get('logs_path', 'logs');

        return $this->modulable->unifyLocalSlashes($this->getRocketeerPath().'/'.$folder);
    }

    /**
     * Get the path to the user's PSR4 folder.
     *
     * @return string
     */
    public function getUserlandPath()
    {
        return $this->modulable->unifyLocalSlashes($this->getRocketeerPath().'/app');
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
        return '/tmp/rocketeer';
        $path = $this->getRocketeerPath().'/storage';

        return $this->modulable->unifySlashes($path);
    }

    /**
     * @return string[]
     */
    public function getProvided()
    {
        return [
            'getConfigurationPath',
            'getDotenvPath',
            'getLogsPath',
            'getProvided',
            'getRocketeerPath',
            'getStoragePath',
            'getUserlandPath',
        ];
    }
}
