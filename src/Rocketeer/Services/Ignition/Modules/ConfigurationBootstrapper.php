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

class ConfigurationBootstrapper extends AbstractBootstrapperModule
{
    /**
     * Load the custom files (tasks, events, ...).
     */
    public function bootstrapConfiguration()
    {
        $this->bootstrapContextualConfiguration();

        $this->bootstrapPlugins();
        $this->bootstrapPluginsConfiguration();
    }

    /**
     * Load any configured plugins
     */
    protected function bootstrapPlugins()
    {
        $plugins = (array) $this->config->get('plugins');
        $plugins = array_filter($plugins, 'class_exists');
        foreach ($plugins as $plugin) {
            $this->container->addServiceProvider($plugin);
        }
    }

    /**
     * Merge the various contextual configurations defined in userland.
     */
    public function bootstrapContextualConfiguration()
    {
        $this->config->replace($this->configurationLoader->getConfiguration());
    }

    /**
     * Merge the plugin configurations defined in userland.
     */
    public function bootstrapPluginsConfiguration()
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
        $configuration = $this->paths->getConfigurationPath();
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
            'bootstrapContextualConfiguration',
            'bootstrapPluginsConfiguration ',
        ];
    }
}
