<?php
namespace Rocketeer\Services\Ignition\Modules;

use Symfony\Component\ClassLoader\Psr4ClassLoader;
use Dotenv\Dotenv;

class UserBootstrapper extends AbstractBootstrapperModule
{
    /**
     * Bootstrap the user's code
     */
    public function bootstrapUserCode()
    {
        $this->bootstrapDotenv();
        $this->bootstrapApp();
        $this->bootstrapStandaloneFiles();
    }

    /**
     * Load the .env file if necessary
     */
    protected function bootstrapDotenv()
    {
        if (!file_exists($this->paths->getDotenvPath())) {
            return;
        }

        $dotenv = new Dotenv($this->paths->getBasePath());
        $dotenv->load();
    }

    /**
     * Load the user's app folder
     */
    protected function bootstrapApp()
    {
        $folder = $this->paths->getAppFolderPath();
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
        $files = $this->files->listContents($folder);
        foreach ($files as $file) {
            dump($file);
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
