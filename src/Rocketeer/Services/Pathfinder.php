<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services;

use Exception;
use Illuminate\Support\Str;
use Rocketeer\Traits\HasLocator;

/**
 * Locates folders and paths on the server and locally.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Pathfinder
{
    use HasLocator;

    //////////////////////////////////////////////////////////////////////
    //////////////////////////////// LOCAL ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get a configured path.
     *
     * @param string $path
     *
     * @return string
     */
    public function getPath($path)
    {
        return $this->rocketeer->getOption('paths.'.$path);
    }

    /**
     * Get the path to the root folder of the application.
     *
     * @return string
     */
    public function getHomeFolder()
    {
        $rootDirectory = $this->rocketeer->getOption('remote.root_directory');
        $rootDirectory = Str::finish($rootDirectory, '/');
        $appDirectory  = $this->rocketeer->getOption('remote.app_directory') ?: $this->rocketeer->getApplicationName();

        return $rootDirectory.$appDirectory;
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
        } else {
            throw new Exception('Cannot determine user home directory.');
        }
    }

    /**
     * Get the base path.
     *
     * @return string
     */
    public function getBasePath()
    {
        $base = $this->app['path.base'] ? $this->app['path.base'].'/' : '';
        $base = $this->unifySlashes($base);

        return $base;
    }

    /**
     * Get the path to the configuration folder.
     *
     * @return string
     */
    public function getConfigurationPath()
    {
        // Return path to Laravel configuration
        if ($this->isInsideLaravel()) {
            $configuration = $this->app['path'].'/config/packages/anahkiasen/rocketeer';
        } else {
            $configuration = $this->app['path.rocketeer.config'];
        }

        return $this->unifyLocalSlashes($configuration);
    }

    /**
     * Get path to the storage folder.
     *
     * @return string
     */
    public function getStoragePath()
    {
        // If no path is bound, default to the Rocketeer folder
        if (!$this->app->bound('path.storage')) {
            return '.rocketeer';
        }

        // Unify slashes
        $storage = $this->app['path.storage'];
        $storage = $this->unifySlashes($storage);
        $storage = str_replace($this->getBasePath(), null, $storage);

        return $storage;
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// SERVER ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the path to a folder, taking into account application name and stage.
     *
     * @param string|null $folder
     *
     * @return string
     */
    public function getFolder($folder = null)
    {
        $folder = $this->replacePatterns($folder);

        $base  = $this->getHomeFolder().'/';
        $stage = $this->connections->getStage();
        if ($folder && $stage) {
            $base .= $stage.'/';
        }
        $folder = str_replace($base, null, $folder);

        return $base.$folder;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Unify the slashes to the UNIX mode (forward slashes).
     *
     * @param string $path
     *
     * @return string
     */
    public function unifySlashes($path)
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * Unify paths to the local DS.
     *
     * @param string $path
     *
     * @return string
     */
    public function unifyLocalSlashes($path)
    {
        return preg_replace('#(/|\\\)#', DS, $path);
    }

    /**
     * Replace patterns in a folder path.
     *
     * @param string $path
     *
     * @return string
     */
    public function replacePatterns($path)
    {
        $base = $this->getBasePath();

        // Replace folder patterns
        return preg_replace_callback('/\{[a-z\.]+\}/', function ($match) use ($base) {
            $folder = substr($match[0], 1, -1);

            // Replace paths from the container
            if ($this->app->bound($folder)) {
                $path = $this->app->make($folder);

                return str_replace($base, null, $this->unifySlashes($path));
            }

            // Replace paths from configuration
            if ($custom = $this->getPath($folder)) {
                return $custom;
            }

            return false;
        }, $path);
    }
}
