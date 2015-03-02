<?php
namespace Rocketeer\Services\Config;

use Illuminate\Support\Arr;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
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
     * @type FileResource[]
     */
    protected $resources = [];

    /**
     * @type string[]
     */
    protected $folders;

    /**
     * @type LoaderInterface
     */
    protected $loader;

    /**
     * @type ConfigurationDefinition
     */
    protected $definition;

    /**
     * @type Processor
     */
    protected $processor;

    /**
     * @type ConfigCache
     */
    protected $cache;

    /**
     * ConfigurationLoader constructor.
     *
     * @param LoaderInterface         $loader
     * @param ConfigurationDefinition $definition
     * @param Processor               $processor
     * @param ConfigCache             $cache
     */
    public function __construct(
        LoaderInterface $loader,
        ConfigurationDefinition $definition,
        Processor $processor,
        ConfigCache $cache
    ) {
        $this->loader     = $loader;
        $this->definition = $definition;
        $this->processor  = $processor;
        $this->cache      = $cache;
    }

    //////////////////////////////////////////////////////////////////////
    //////////////////////// CONFIGURATION FOLDERS ///////////////////////
    //////////////////////////////////////////////////////////////////////

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

    //////////////////////////////////////////////////////////////////////
    /////////////////////////// CONFIGURATION ////////////////////////////
    //////////////////////////////////////////////////////////////////////

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
        $this->resources      = [];

        // Return cached version if available
        if ($this->cache->isFresh()) {
            return $this->getFromCache();
        }

        // Load in memory the files from all configurations
        $folders = array_filter($this->folders, 'is_dir');
        foreach ($folders as $folder) {
            $this->configurations[$folder] = [];
            $this->loadConfigurationFor($folder);
            $this->loadContextualConfigurationsFor($folder);
        }

        // Merge additional configurations
        if ($configurations) {
            $this->configurations = array_merge($this->configurations, $configurations);
        }

        // Merge, process and validate configuration
        $processed = $this->processor->processConfiguration(
            $this->definition,
            $this->configurations
        );

        // Cache configuration
        $this->writeToCache($processed);

        return $processed;
    }

    /**
     * @param string $folder
     */
    protected function loadConfigurationFor($folder)
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

            // Add to cache
            $this->resources[] = new FileResource($file->getPathname());

            $contents = $this->loader->load($file->getPathname());
            // $contents = $this->autoWrap($file, $contents);

            $this->configurations[$folder][$key] = $contents;
        }
    }

    /**
     * @param string $folder
     */
    protected function loadContextualConfigurationsFor($folder)
    {
        $contextual = (new Finder())->in($folder)->name('/(stages|connections)/')->directories();
        foreach ($contextual as $type) {
            /** @type SplFileInfo[] $files */
            $files = (new Finder())->in($type->getPathname())->files();

            foreach ($files as $file) {
                $key = str_replace($folder.DS, null, $file->getPathname());
                $key = vsprintf('config.on.%s.%s', explode(DS, $key));

                // Add to cache
                $this->resources[] = new FileResource($file->getPathname());

                // Load contents and merge
                $contents = include $file->getPathname();
                $contents = $this->autoWrap($file, $contents);
                $current  = Arr::get($this->configurations[$folder], $key, []);
                $contents = $current ? array_replace_recursive($current, $contents) : $contents;

                Arr::set($this->configurations[$folder], $key, $contents);
            }
        }
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// CACHING ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Automatically wrap configuration in their arrays
     *
     * @param SplFileInfo $file
     * @param array       $contents
     *
     * @return array
     */
    protected function autoWrap(SplFileInfo $file, array $contents)
    {
        $key = $file->getBasename('.'.$file->getExtension());
        if (array_keys($contents) !== [$key] || !is_array($contents[$key])) {
            return [$key => $contents];
        }

        return $contents;
    }

    /**
     * Cache the configuration
     *
     * @param array $processed
     */
    protected function writeToCache(array $processed)
    {
        $processed = serialize($processed);

        $this->cache->write($processed, $this->resources);
    }

    /**
     * @return array
     */
    protected function getFromCache()
    {
        $file = $this->cache->__toString();

        // Get an unserialize
        $configuration = file_get_contents($file);
        $configuration = unserialize($configuration);

        return $configuration;
    }
}
