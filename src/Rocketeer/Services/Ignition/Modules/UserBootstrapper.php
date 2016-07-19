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

use Dotenv\Dotenv;
use Symfony\Component\ClassLoader\Psr4ClassLoader;

class UserBootstrapper extends AbstractBootstrapperModule
{
    /**
     * Bootstrap the user's code.
     */
    public function bootstrapUserCode()
    {
        $this->bootstrapApp();
        $this->bootstrapStandaloneFiles();
    }

    /**
     * Load the user's app folder.
     */
    protected function bootstrapApp()
    {
        $folder = $this->paths->getUserlandPath();
        if (!$this->files->has($folder)) {
            return;
        }

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

    protected function bootstrapStandaloneFiles()
    {
        $folder = $this->paths->getRocketeerPath();
        $appFolderPath = trim($this->paths->getUserlandPath(), '/');
        $files = $this->files->listContents($folder, true);

        // Gather files to include in the correct order
        $queue = [];
        foreach ($files as $file) {
            $path = $file['path'];
            $isDir = $file['type'] === 'dir';
            $isPhp = !$isDir && isset($file['extension']) ? $file['extension'] === 'php' : false;

            if ($isDir || !$isPhp || strpos($path, $appFolderPath) !== false) {
                continue;
            }

            // Load tasks first
            if (strpos($path, 'tasks') !== false) {
                array_unshift($queue, $path);
            } else {
                $queue[] = $path;
            }
        }

        // Include files
        foreach ($queue as $file) {
            $this->files->includeFile($file);
        }
    }

    /**
     * @return string[]
     */
    public function getProvided()
    {
        return [
            'bootstrapUserCode',
        ];
    }
}
