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

use Rocketeer\Traits\ContainerAwareTrait;

/**
 * Publishes the plugin's configurations in user-land.
 */
class PluginsIgniter
{
    use ContainerAwareTrait;

    /**
     * Publishes a package's configuration.
     *
     * @param string $package
     *
     * @return bool|string|null
     */
    public function publish($package)
    {
        // Find the plugin's configuration
        $paths = $this->findPackageConfiguration($package);

        // Cancel if no valid paths
        $paths = array_filter($paths, [$this->files, 'isDirectory']);
        $paths = array_values($paths);
        if (empty($paths)) {
            return $this->explainer->error('No configuration found for '.$package);
        }

        return $this->publishConfiguration($paths[0]);
    }

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
            if ($this->files->has($destination) && !$this->command->confirm('File '.$file['basename'].' already exists, replace?')) {
                continue;
            }

            $this->files->forceCopy($file['path'], $fileDestination);
            $this->command->writeln('Published <comment>' .str_replace($this->paths->getBasePath(), null, $fileDestination). '</comment>');
        }
    }
}
