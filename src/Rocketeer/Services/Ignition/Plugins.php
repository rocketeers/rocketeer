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
use Rocketeer\Traits\HasLocator;

/**
 * Publishes the plugin's configurations in user-land.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Plugins
{
    use HasLocator;

    /**
     * Publishes a package's configuration.
     *
     * @param string $package
     *
     * @return bool|null
     */
    public function publish($package)
    {
        if ($this->isInsideLaravel()) {
            return $this->publishLaravelConfiguration($package);
        }

        // Find the plugin's configuration
        $paths = $this->findPackageConfiguration($package);
        $paths = array_filter($paths, [$this->files, 'isDirectory']);
        $paths = array_values($paths);

        // Cancel if no valid paths
        if (empty($paths)) {
            return $this->command->error('No configuration found for '.$package);
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
            $this->app['path.base'].'/vendor/%s/src/config',
            $this->app['path.base'].'/vendor/%s/config',
            $this->paths->getUserHomeFolder().'/.rocketeer/vendor/%s/src/config',
            $this->paths->getUserHomeFolder().'/.rocketeer/vendor/%s/config',
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
     * Publishes a configuration within a Laravel application.
     *
     * @param string $package
     *
     * @return bool
     */
    protected function publishLaravelConfiguration($package)
    {
        // Publish initial configuration
        $this->artisan->call('config:publish', ['package' => $package]);

        // Move under Rocketeer namespace
        $path        = $this->app['path'].'/config/packages/'.$package;
        $destination = preg_replace('/packages\/([^\/]+)/', 'packages/rocketeers', $path);

        return $this->files->move($path, $destination);
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
        // Get the vendor and package
        preg_match('/vendor\/([^\/]+)\/([^\/]+)/', $path, $handle);
        $handle  = (array) $handle;
        $package = Arr::get($handle, 2);

        // Compute and create the destination foldser
        $destination = $this->app['path.rocketeer.config'];
        $destination = $destination.'/plugins/rocketeers/'.$package;
        if (!$this->files->isDirectory($destination)) {
            $this->files->makeDirectory($destination, 0755, true);
        }

        // Display success
        $this->explainer->success('Publishing configuration from '.$path.' to '.$destination);

        return $this->files->copyDirectory($path, $destination);
    }
}
