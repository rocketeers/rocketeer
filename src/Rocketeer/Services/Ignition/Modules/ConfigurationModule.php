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

namespace Rocketeer\Services\Ignition\Modules;

class ConfigurationModule extends AbstractBootstrapperModule
{
    /**
     * Load the custom files (tasks, events, ...).
     */
    public function bootstrapConfiguration()
    {
        // Load .env file
        if (file_exists($this->paths->getDotenvPath())) {
            $dotenv = new Dotenv($this->paths->getBasePath());
            $dotenv->load();
        }

        // Load user namespace and files
        $folder = $this->paths->getAppFolderPath();
        if ($this->files->has($folder)) {
            $namespace = ucfirst($this->config->get('application_name'));

            // Load main namespace
            $classloader = new Psr4ClassLoader();
            $classloader->addPrefix($namespace.'\\', $folder);
            $classloader->register();

            // Load service provider
            $serviceProvider = $namespace.'\\'.$namespace.'ServiceProvider';
            if (class_exists($serviceProvider)) {
                $this->container->addServiceProvider($serviceProvider);
            }
        }

        // Load plugins
        $plugins = (array) $this->config->get('plugins');
        $plugins = array_filter($plugins, 'class_exists');
        foreach ($plugins as $plugin) {
            $this->container->addServiceProvider($plugin);
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

    ////////////////////////////////////////////////////////////////////
    ///////////////////////////// CONFIGURATION ////////////////////////
    ////////////////////////////////////////////////////////////////////

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
        $configuration = $this->container->get('path.rocketeer.config');
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
            $handle = $computeHandle($file);

            $this->config->set($handle, $contents);
        }
    }

    /**
     * @return string[]
     */
    public function getProvided()
    {
        return [
            'bootstrapConfiguration',
            'mergeContextualConfigurations',
            'mergePluginsConfiguration ',
        ];
    }
}
