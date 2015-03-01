<?php
namespace Rocketeer\Services\Config;

use Illuminate\Support\Arr;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ConfigurationLoader
{
    /**
     * The various found configurations
     *
     * @type array
     */
    protected $configurations = [];

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
     * @param Processor               $processor
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
     * Get a final merged version of the configuration,
     * taking into account defaults, user and contextual
     * configurations
     *
     * @return array
     */
    public function getConfiguration()
    {
        foreach ($this->folders as $folder) {
            if (!is_dir($folder)) {
                continue;
            }

            $this->loadConfigurationFor($folder);
            $this->loadContextualConfigurationsFor($folder);
        }

        return $this->processor->processConfiguration(
            $this->definition,
            $this->configurations
        );
    }

    /**
     * @param string $folder
     */
    private function loadContextualConfigurationsFor($folder)
    {
        $contextual = (new Finder())->in($folder)->name('/(stages|connections)/')->directories();
        foreach ($contextual as $type) {
            /** @type SplFileInfo[] $files */
            $files = (new Finder())->in($type->getPathname())->files();
            
            foreach ($files as $file) {
                $key = str_replace($folder.DS, null, $file->getPathname());
                $key = vsprintf('config.on.%s.%s', explode(DS, $key));

                // Load contents and merge
                $contents = include $file->getPathname();
                $contents = array_replace_recursive(Arr::get($this->configurations[$folder], $key, []), $contents);

                Arr::set($this->configurations[$folder], $key, $contents);
            }
        }
    }

    /**
     * @param string $folder
     */
    private function loadConfigurationFor($folder)
    {
        /** @type SplFileInfo[] $files */
        $files = (new Finder())
            ->in($folder)
            ->name('*.php')
            ->exclude(['connections', 'stages'])
            ->notName('/(events|tasks)\.php/')
            ->sortByName()
            ->files();

        // Load base files
        foreach ($files as $file) {
            $key = $file->getBasename('.php');

            $this->configurations[$folder][$key] = $this->loader->load($file->getPathname());
        }
    }
}
