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

namespace Rocketeer\Services\Ignition;

use ReflectionClass;
use Rocketeer\Traits\ContainerAwareTrait;

/**
 * Publishes the plugin's configurations in user-land.
 */
class PluginsIgniter
{
    use ContainerAwareTrait;

    /**
     * @var bool
     */
    protected $force = false;

    /**
     * @param bool $force
     */
    public function setForce($force)
    {
        $this->force = $force;
    }

    ////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////// PUBLISHING //////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Publishes a package's configuration.
     *
     * @param string|string[] $plugins
     *
     * @return bool|string|null
     */
    public function publish($plugins = null)
    {
        $plugins = $this->gatherLoadedPackagesHandles($plugins);
        foreach ($plugins as $plugin) {
            // Find the plugin's configuration
            $paths = $this->findPackageConfiguration($plugin);

            // Cancel if no valid paths
            $paths = array_filter($paths, [$this->files, 'isDirectory']);
            $paths = array_values($paths);
            if (empty($paths)) {
                $this->explainer->comment('No configuration found for '.$plugin);
                continue;
            }

            $this->publishConfiguration($paths[0]);
        }
    }

    /**
     * Publishes a configuration within a classic application.
     *
     * @param string $path
     *
     * @return bool
     */
    protected function publishConfiguration($path)
    {
        // Compute and create the destination folder
        $destination = $this->paths->getConfigurationPath().'/plugins';

        // Export configuration
        $files = $this->files->listFiles($path, true);
        foreach ($files as $file) {
            $fileDestination = $destination.DS.$file['basename'];
            if ($this->files->has($destination) && !$this->force) {
                continue;
            }

            $this->files->forceCopy($file['path'], $fileDestination);
            $this->explainer->success('Published <comment>'.str_replace($this->paths->getBasePath(), null, $fileDestination).'</comment>');
        }
    }

    ////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////// HELPERS ////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Find all the possible locations for a package's configuration.
     *
     * @param string $package
     *
     * @return string[]
     */
    public function findPackageConfiguration($package)
    {
        $paths = [
            $this->paths->getBasePath().'vendor/%s/src/config',
            $this->paths->getBasePath().'vendor/%s/config',
            $this->paths->getRocketeerPath().'/vendor/%s/src/config',
            $this->paths->getRocketeerPath().'/vendor/%s/config',
            $this->paths->getUserHomeFolder().'/.composer/vendor/%s/src/config',
            $this->paths->getUserHomeFolder().'/.composer/vendor/%s/config',
        ];

        // Check for the first configuration path that exists
        $paths = array_map(function ($path) use ($package) {
            return sprintf($path, $package);
        }, $paths);

        return $paths;
    }

    /**
     * Infer the name of the loaded packages
     * from their service provider.
     *
     * @param string|string[] $packages
     *
     * @return string[]
     */
    protected function gatherLoadedPackagesHandles($packages)
    {
        $packages = (array) $packages;
        if (!$packages) {
            $plugins = $this->container->getPlugins();
            foreach ($plugins as $plugin) {
                $path = (new ReflectionClass($plugin))->getFileName();
                preg_match('/vendor\/([^\/]+)\/([^\/]+)/', $path, $handle);
                if (count($handle) !== 3) {
                    continue;
                }

                $packages[] = $handle[1].'/'.$handle[2];
            }
        }

        return $packages;
    }
}
