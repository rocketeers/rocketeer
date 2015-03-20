<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\Config;

use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use Rocketeer\Traits\HasLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ConfigurationLoader
{
    use HasLocator;

    /**
     * The various found configurations.
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
     * @type ConfigurationCache
     */
    protected $cache;

    /**
     * ConfigurationLoader constructor.
     *
     * @param Container               $app
     * @param LoaderInterface         $loader
     * @param ConfigurationDefinition $definition
     * @param Processor               $processor
     * @param ConfigurationCache      $cache
     */
    public function __construct(
        Container $app,
        LoaderInterface $loader,
        ConfigurationDefinition $definition,
        Processor $processor,
        ConfigurationCache $cache
    ) {
        $this->app        = $app;
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
     * configurations.
     *
     * @param array $configurations Additional configurations to merge
     *
     * @return array
     */
    public function getConfiguration(array $configurations = [])
    {
        $this->configurations = [];
        $this->resources      = [];

        // Get which files are present in the configurations
        $folders = array_filter($this->folders, [$this->files, 'isDirectory']);
        if (!$folders) {
            return [];
        }

        $files = $this->getFinder($folders);
        $files = array_keys(iterator_to_array($files));

        // Return cached version if available
        if ($this->cache->isFresh()) {
            $cache = $this->cache->getContents();

            // If the files the configuration was cached from
            // match the ones we have, return the cache
            if (Arr::get($cache, 'meta') === $files) {
                return $cache;
            }
        }

        // Load in memory the files from all configurations
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

        $processed['meta'] = $files;

        // Cache configuration
        $this->cache->write($processed, $this->resources);

        return $processed;
    }

    /**
     * @param string $folder
     */
    protected function loadConfigurationFor($folder)
    {
        /** @type SplFileInfo[] $files */
        $files = $this->getFinder($folder)
                      ->exclude(['connections', 'stages', 'plugins', 'tasks', 'events', 'strategies']);

        // Load base files
        foreach ($files as $file) {
            // Load file
            $contents = $this->loader->load($file->getPathname());
            $contents = $this->autoWrap($file, $contents);
            if (!is_array($contents)) {
                continue;
            }

            // Add to cache
            $this->resources[] = new FileResource($file->getPathname());

            $this->configurations[$folder] = array_merge($this->configurations[$folder], $contents);
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
     * Flush the cache.
     */
    public function flushCache()
    {
        $this->cache->flush();
    }

    /**
     * Automatically wrap configuration in their arrays.
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
     * @param string|string[] $folders
     *
     * @return Finder|SplFileInfo[]
     */
    protected function getFinder($folders)
    {
        return (new Finder())
            ->in($folders)
            ->name('*.php')
            ->notName('/(events|tasks)\.php/')
            ->sortByName()
            ->files();
    }
}
