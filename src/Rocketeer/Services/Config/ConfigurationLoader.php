<?php
namespace Rocketeer\Services\Config;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Finder\Finder;

class ConfigurationLoader
{
    /**
     * @type string[]
     */
    private $folders;

    /**
     * @type LoaderInterface
     */
    private $loader;

    /**
     * @type ConfigurationDefinition
     */
    private $definition;

    /**
     * @type Processor
     */
    private $processor;

    /**
     * ConfigurationLoader constructor.
     *
     * @param LoaderInterface         $loader
     * @param ConfigurationDefinition $definition
     */
    public function __construct(LoaderInterface $loader, ConfigurationDefinition $definition, Processor $processor)
    {
        $this->loader     = $loader;
        $this->definition = $definition;
        $this->processor  = $processor;
    }

    /**
     * @return string[]
     */
    public function getFolders()
    {
        return $this->folders;
    }

    /**
     * @param string[] $folders
     */
    public function setFolders($folders)
    {
        $this->folders = $folders;
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        $configurations = [];
        foreach ($this->folders as $folder) {
            if (!is_dir($folder)) {
                continue;
            }

            $finder = (new Finder())->in($folder);
            foreach ($finder as $file) {
                $key                           = $file->getBasename('.php');
                $configurations[$folder][$key] = $this->loader->load($file->getPathname());
            }
        }

        return $this->processor->processConfiguration(
            $this->definition,
            $configurations
        );
    }
}
