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

namespace Rocketeer\Services\Config\Files\Loaders;

use Illuminate\Support\Arr;
use Rocketeer\Services\Config\ConfigurationDefinition;
use Rocketeer\Services\Config\Files\Finders\AbstractConfigurationFinder;
use Rocketeer\Services\Config\Files\Finders\ConsolidatedConfigurationFinder;
use Rocketeer\Services\Config\Files\Finders\ContextualConfigurationFinder;
use Rocketeer\Services\Config\Files\Finders\MainConfigurationFinder;
use Rocketeer\Services\Config\Files\Finders\PluginsConfigurationFinder;
use Rocketeer\Services\Container\Container;
use Rocketeer\Traits\ContainerAwareTrait;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Standard implementation for the loading of configuration
 * from a set of folders.
 */
class ConfigurationLoader implements ConfigurationLoaderInterface
{
    use ContainerAwareTrait;

    /**
     * @var string[]
     */
    protected $folders;

    /**
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * @var ConfigurationDefinition
     */
    protected $definition;

    /**
     * @var Processor
     */
    protected $processor;

    /**
     * ConfigurationLoader constructor.
     *
     * @param Container               $container
     * @param LoaderInterface         $loader
     * @param ConfigurationDefinition $definition
     * @param Processor               $processor
     *
     * @internal param ConfigurationCache $cache
     */
    public function __construct(
        Container $container,
        LoaderInterface $loader,
        ConfigurationDefinition $definition,
        Processor $processor
    ) {
        $this->container = $container;
        $this->loader = $loader;
        $this->definition = $definition;
        $this->processor = $processor;
    }

    //////////////////////////////////////////////////////////////////////
    //////////////////////// CONFIGURATION FOLDERS ///////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return string[]
     */
    public function getFolders()
    {
        $folders = array_filter($this->folders, [$this->files, 'isDirectory']);
        $folders = array_filter($folders);
        $folders = array_values($folders);

        return $folders;
    }

    /**
     * {@inheritdoc}
     */
    public function setFolders(array $folders)
    {
        $this->folders = $folders;
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////////// CONFIGURATION ////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        $configurations = [];

        // Gather the files to load and their paths
        $files = $this->getFiles();
        foreach ($files as $key => $file) {
            $configurations = $this->loadConfigurationFile($configurations, $file, is_string($key) ? $key : null);
        }

        return $this->processor->processConfiguration($this->definition, $configurations);
    }

    /**
     * Gather the files to load for configuration.
     *
     * @return SplFileInfo[]
     */
    public function getFiles()
    {
        $finders = [
            MainConfigurationFinder::class,
            ContextualConfigurationFinder::class,
            PluginsConfigurationFinder::class,
        ];

        // Filter out non-existing folders
        $folders = $this->getFolders();
        if (!$folders) {
            return [];
        }

        $files = [];
        foreach ($folders as $folder) {
            foreach ($finders as $finder) {
                /** @var AbstractConfigurationFinder $finder */
                $finder = new $finder($this->files, $folder);
                $files = array_merge($files, $finder->getFiles());
            }
        }

        // Load consolidated configuration
        $folder = $this->paths->getRocketeerPath();
        if ($this->files->has($folder)) {
            $consolidated = new ConsolidatedConfigurationFinder($this->files, $folder);
            $files = array_merge($files, $consolidated->getFiles());
        }

        return $files;
    }

    ////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////// LOADING ////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * @param array       $configuration
     * @param SplFileInfo $file
     * @param string|null $key
     *
     * @return array|void
     */
    protected function loadConfigurationFile(array $configuration, SplFileInfo $file, $key = null)
    {
        $pathname = $file->getPathname();
        $contents = $this->loader->load($pathname);
        $contents = mb_strpos($key, 'plugins') === false ? $this->wrapConfigurationContents($file, $contents) : $contents;
        if (!is_array($contents)) {
            return $configuration;
        }

        // Since non-PHP files don't support closures
        // unset them to pass configuration validation
        if ($file->getExtension() !== 'php') {
            unset($contents['config']['logs'], $contents['remote']['permissions']['callback']);
        }

        // Append configuration to the queue
        $configuration[$pathname] = [];
        Arr::set($configuration[$pathname], $key, $contents);

        return $configuration;
    }

    /**
     * Automatically wrap configuration in their arrays.
     *
     * @param SplFileInfo $file
     * @param array       $contents
     *
     * @return array
     */
    protected function wrapConfigurationContents(SplFileInfo $file, array $contents)
    {
        $key = $file->getBasename('.'.$file->getExtension());
        if (array_keys($contents) !== [$key] || !is_array($contents[$key])) {
            return [$key => $contents];
        } elseif (array_keys($contents) === ['rocketeer']) {
            return $contents['rocketeer'];
        }

        return $contents;
    }
}
