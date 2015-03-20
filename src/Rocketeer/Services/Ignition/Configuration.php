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

use Illuminate\Support\Arr;
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
        $this->loadFileOrFolder('tasks');
        $this->loadFileOrFolder('events');
        $this->loadFileOrFolder('strategies');

        // Load plugins
        $plugins = (array) $this->config->get('plugins');
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
        $this->config->replace($this->configurationLoader->getConfiguration());
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
        $destination = $this->paths->getConfigurationPath();
        $format      = $this->getOption('format', true) ?: 'php';

        // Create directory
        if (!$this->files->isDirectory($destination)) {
            $this->files->createDir($destination);
        }

        // Consolidate or not configuration
        if ($this->getOption('consolidated', true)) {
            $destination .= '/config.'.$format;
        }

        // Unzip configuration files
        $this->configurationPublisher->publish($destination, $format);

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
        $folder = strpos($folder, 'config.') !== false ? dirname($folder) : $folder;
        $files  = (array) $this->files->listContents($folder, true);
        foreach ($files as $file) {
            foreach ($values as $name => $value) {
                $pattern  = '{'.$name.'}';
                $contents = $this->files->read($file['path']);

                if (strpos($contents, $pattern) !== false) {
                    $contents = str_replace($pattern, $value, $contents);
                    $this->files->put($file['path'], $contents);
                }
            }
        }

        // Change repository in use
        $application = Arr::get($values, 'application_name');
        $this->localStorage->setFile($application);
    }

    /**
     * Merge configuration files from userland.
     *
     * @param string[]    $folders
     * @param callable    $computeHandle
     * @param string|null $exclude
     */
    protected function mergeConfigurationFolders(array $folders, callable $computeHandle, $exclude = null)
    {
        // Cancel if not ignited yet
        $configuration = $this->app['path.rocketeer.config'];
        if (!$this->files->isDirectory($configuration)) {
            return;
        }

        // Cancel if the subfolders don't exist
        $existing = array_filter($folders, function ($path) use ($configuration) {
            return $this->files->isDirectory($configuration.DS.$path);
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
        if ($this->getFramework() === 'laravel') {
            $path    = $this->paths->getConfigurationPath();
            $storage = $this->paths->getStoragePath();
        } else {
            $path    = $this->paths->getBasePath().'.rocketeer';
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
            if (!$this->files->isDirectory($file) && $this->files->has($file.'.php')) {
                $file .= '.php';
            }

            // Use configuration in current folder if none found
            $realpath = realpath('.').DS.basename($file);
            if (!$this->files->has($file) && $this->files->has($realpath)) {
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

        return sprintf('on.%s', $handle);
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
        if (!$this->files->isDirectory($file) && $this->files->has($file) && $file !== 'strategies.php') {

            $this->files->include($file);
        } // Else include its contents
        elseif ($this->files->isDirectory($file) && $this->files->has($file)) {
            $files = (new Finder())->in($this->files->getAdapter()->applyPathPrefix($file))->name('*.php')->files();
            foreach ($files as $file) {
                $this->files->include($this->files->getAdapter()->removePathPrefix($file));
            }
        }
    }
}
