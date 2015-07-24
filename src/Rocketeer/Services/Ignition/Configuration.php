<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\Ignition;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Rocketeer\Facades;
use Rocketeer\Traits\HasLocator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Ignites Rocketeer's custom configuration, tasks, events and paths
 * depending on what Rocketeer is used on.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Configuration
{
    use HasLocator;

    /**
     * Bind paths to the container.
     */
    public function bindPaths()
    {
        $this->bindBase();
        $this->bindConfiguration();
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////// USER CONFIGURATION /////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Load the custom files (tasks, events, ...).
     */
    public function loadUserConfiguration()
    {
        $fileLoaders = function () {
            $this->loadFileOrFolder('tasks');
            $this->loadFileOrFolder('events');
            $this->loadFileOrFolder('strategies');
        };

        // Defer loading of tasks and events or not
        if (is_a($this->app, 'Illuminate\Foundation\Application')) {
            $this->app->booted($fileLoaders);
        } else {
            $fileLoaders();
        }

        // Load plugins
        $plugins = (array) $this->config->get('rocketeer::plugins');
        $plugins = array_filter($plugins, 'class_exists');
        foreach ($plugins as $plugin) {
            $this->tasks->plugin($plugin);
        }

        // Merge contextual configurations
        $this->mergeContextualConfigurations();
        $this->mergePluginsConfiguration();
    }

    /**
     * Merge the various contextual configurations defined in userland.
     */
    public function mergeContextualConfigurations()
    {
        $this->mergeConfigurationFolders(['stages', 'connections'], function (SplFileInfo $file) {
            return $this->computeHandleFromPath($file);
        }, 'config.php');
    }

    /**
     * Merge the plugin configurations defined in userland.
     */
    public function mergePluginsConfiguration()
    {
        $this->mergeConfigurationFolders(['plugins'], function (SplFileInfo $file) {
            $handle = basename(dirname($file->getPathname()));
            $handle .= '::'.$file->getBasename('.php');

            return $handle;
        });
    }

    /**
     * Export the configuration files.
     *
     * @return string
     */
    public function exportConfiguration()
    {
        $source      = $this->paths->unifyLocalSlashes(__DIR__.'/../../../config');
        $source      = Str::contains($source, 'phar://') ? $source : realpath($source);
        $destination = $this->paths->getConfigurationPath();

        // Unzip configuration files
        $this->files->copyDirectory($source, $destination);

        return $destination;
    }

    ////////////////////////////////////////////////////////////////////
    ///////////////////////////// CONFIGURATION ////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Replace placeholders in configuration.
     *
     * @param string   $folder
     * @param string[] $values
     */
    public function updateConfiguration($folder, array $values = [])
    {
        // Replace stub values in files
        $files = $this->files->files($folder);
        foreach ($files as $file) {
            foreach ($values as $name => $value) {
                $contents = str_replace('{'.$name.'}', $value, file_get_contents($file));
                $this->files->put($file, $contents);
            }
        }

        // Change repository in use
        $application = Arr::get($values, 'application_name');
        $this->localStorage->setFile($application);
    }

    /**
     * Merge configuration files from userland.
     *
     * @param array       $folders
     * @param callable    $computeHandle
     * @param string|null $exclude
     */
    protected function mergeConfigurationFolders(array $folders, Closure $computeHandle, $exclude = null)
    {
        // Cancel if not ignited yet
        $configuration = $this->app['path.rocketeer.config'];
        if (!is_dir($configuration)) {
            return;
        }

        // Cancel if the subfolders don't exist
        $existing = array_filter($folders, function ($path) use ($configuration) {
            return is_dir($configuration.DS.$path);
        });
        if (!$existing) {
            return;
        }

        // Get folders to glob
        $folders = $this->paths->unifyLocalSlashes($configuration.'/{'.implode(',', $folders).'}/*');

        // Gather custom files
        $finder = new Finder();
        $finder = $finder->in($folders);
        if ($exclude) {
            $finder = $finder->notName($exclude);
        }

        // Bind their contents to the "on" array
        $files = $finder->files();
        foreach ($files as $file) {
            $contents = include $file->getPathname();
            $handle   = $computeHandle($file);

            $this->config->set($handle, $contents);
        }
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// PATHS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Bind the base path to the Container.
     */
    protected function bindBase()
    {
        if ($this->app->bound('path.base')) {
            return;
        }

        $this->app->instance('path.base', getcwd());
    }

    /**
     * Bind paths to the configuration files.
     */
    protected function bindConfiguration()
    {
        // Bind path to the configuration directory
        if ($this->isInsideLaravel()) {
            $path    = $this->paths->getConfigurationPath();
            $storage = $this->paths->getStoragePath();
        } else {
            $path = $this->paths->getBasePath().'.rocketeer';

            $storage = $path;
        }

        // Build paths
        $paths = [
            'config'     => $path.'',
            'events'     => $path.DS.'events',
            'plugins'    => $path.DS.'plugins',
            'strategies' => $path.DS.'strategies',
            'tasks'      => $path.DS.'tasks',
            'logs'       => $storage.DS.'logs',
        ];

        foreach ($paths as $key => $file) {

            // Check whether we provided a file or folder
            if (!is_dir($file) && file_exists($file.'.php')) {
                $file .= '.php';
            }

            // Use configuration in current folder if none found
            $realpath = realpath('.').DS.basename($file);
            if (!file_exists($file) && file_exists($realpath)) {
                $file = $realpath;
            }

            $this->app->instance('path.rocketeer.'.$key, $file);
        }
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// HELPERS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Computes which configuration handle a config file should bind to.
     *
     * @param SplFileInfo $file
     *
     * @return string
     */
    protected function computeHandleFromPath(SplFileInfo $file)
    {
        // Get realpath
        $handle = $file->getRealpath();

        // Format appropriately
        $handle = str_replace($this->app['path.rocketeer.config'].DS, null, $handle);
        $handle = str_replace('.php', null, $handle);
        $handle = str_replace(DS, '.', $handle);

        return sprintf('rocketeer::on.%s', $handle);
    }

    /**
     * Load a file or its contents if a folder.
     *
     * @param string $handle
     */
    protected function loadFileOrFolder($handle)
    {
        // Bind ourselves into the facade to avoid automatic resolution
        Facades\Rocketeer::setFacadeApplication($this->app);

        // If we have one unified tasks file, include it
        $file = $this->app['path.rocketeer.'.$handle];
        if (!is_dir($file) && file_exists($file) && $file !== 'strategies.php') {
            include $file;
        } // Else include its contents
        elseif (is_dir($file)) {
            $folder = glob($file.DS.'*.php');
            foreach ($folder as $file) {
                include $file;
            }
        }
    }
}
