<?php
namespace Rocketeer\Services\Environment\Pathfinders;

use Exception;
use Illuminate\Support\Str;

class LocalPathfinder extends AbstractPathfinder
{
    /**
     * Get the path to the users home folder.
     *
     * @throws Exception
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
     * Get the default path for the SSH key.
     *
     * @throws Exception
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
        $app = $this->getPath('app').'/' ?: $this->getBasePath();
        $app = $this->unifySlashes($app);

        return $app;
    }

    /**
     * Get the path to the configuration folder.
     *
     * @return string
     */
    public function getConfigurationPath()
    {
        // Get path to configuration
        $framework     = $this->getFramework();
        $configuration = $framework ? $framework->getConfigurationPath() : $this->app['path.rocketeer.config'];

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

    /**
     * The methods this pathfinder provides
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'getApplicationPath',
            'getConfigurationCachePath',
            'getConfigurationPath',
            'getDefaultKeyPath',
            'getRocketeerConfigFolder',
            'getStoragePath',
            'getUserHomeFolder',
        ];
    }
}
