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
     * @param string $folder
     */
    public function addFolder($folder)
    {
        $this->folders[] = $folder;
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
     * @param array $configurations Additional configurations to merge
     *
     * @return array
     */
    public function getConfiguration(array $configurations = [])
    {
        $this->configurations = [];
        foreach ($this->folders as $folder) {
            if (!is_dir($folder)) {
                continue;
            }

            $this->configurations[$folder] = [];
            $this->loadConfigurationFor($folder);
            $this->loadContextualConfigurationsFor($folder);
        }

        // Merge additional configurations
        if ($configurations) {
            $this->configurations = array_merge($this->configurations, $configurations);
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
                $contents = $this->autoWrap($file, $contents);
                $current  = Arr::get($this->configurations[$folder], $key, []);
                $contents = $current ? array_replace_recursive($current, $contents) : $contents;

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
            ->exclude(['connections', 'stages', 'tasks', 'events', 'strategies'])
            ->notName('/(events|tasks)\.php/')
            ->sortByName()
            ->files();

        // Load base files
        foreach ($files as $file) {
            $key = $file->getBasename('.php');

            $contents = $this->loader->load($file->getPathname());
            // $contents = $this->autoWrap($file, $contents);

            $this->configurations[$folder][$key] = $contents;
        }
    }

    /**
     * Automatically wrap configuration in their arrays
     *
     * @param SplFileInfo $file
     * @param array       $contents
     *
     * @return array
     */
    private function autoWrap(SplFileInfo $file, array $contents)
    {
        $key = $file->getBasename('.'.$file->getExtension());
        if (array_keys($contents) !== [$key] || !is_array($contents[$key])) {
            return [$key => $contents];
        }

        return $contents;
    }
}
