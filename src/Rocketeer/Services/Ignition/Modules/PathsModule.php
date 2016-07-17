<?php
namespace Rocketeer\Services\Ignition\Modules;

class PathsModule extends AbstractBootstrapperModule
{
    /**
     * Bind paths to the container.
     */
    public function bootstrapPaths()
    {
        $this->bindBase();
        $this->bindConfiguration();
    }

    /**
     * Bind the base path to the Container.
     */
    protected function bindBase()
    {
        if ($this->container->has('path.base')) {
            return;
        }

        $this->container->add('path.base', getcwd());
    }

    /**
     * Bind paths to the configuration files.
     */
    protected function bindConfiguration()
    {
        // Bind path to the configuration directory
        $path = $this->paths->getBasePath().'.rocketeer';

        // Build paths
        $paths = [
            'config' => $path.'',
            'events' => $path.DS.'events',
            'plugins' => $path.DS.'plugins',
            'strategies' => $path.DS.'strategies',
            'tasks' => $path.DS.'tasks',
            'logs' => $path.DS.'logs',
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

            $this->container->share('path.rocketeer.'.$key, $file);
        }
    }

    /**
     * @return string[]
     */
    public function getProvided()
    {
        return [
            'bootstrapPaths',
        ];
    }
}
