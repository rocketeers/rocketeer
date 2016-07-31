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
use Rocketeer\Services\Config\Files\ConfigurationCache;
use Rocketeer\Traits\ContainerAwareTrait;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Decorator around a ConfigurationLoader that caches its contents
 * and checks for freshness.
 */
class CachedConfigurationLoader implements ConfigurationLoaderInterface
{
    use ContainerAwareTrait;

    /**
     * @var ConfigurationCache
     */
    protected $cache;

    /**
     * @var ConfigurationLoaderInterface
     */
    protected $loader;

    /**
     * @var FileResource[]
     */
    protected $resources = [];

    /**
     * @param ConfigurationCache           $cache
     * @param ConfigurationLoaderInterface $loader
     */
    public function __construct(ConfigurationCache $cache, ConfigurationLoaderInterface $loader)
    {
        $this->cache = $cache;
        $this->loader = $loader;
    }

    /**
     * @param string[] $folders
     */
    public function setFolders(array $folders)
    {
        $this->loader->setFolders($folders);
    }

    /**
     * @return string[]
     */
    public function getFolders()
    {
        return $this->loader->getFolders();
    }

    /**
     * @return SplFileInfo[]
     */
    public function getFiles()
    {
        return $this->loader->getFiles();
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        // Gather paths to files to load
        $files = $this->loader->getFiles();
        foreach ($files as &$file) {
            $file = $file->getPathname();
        }

        // Create resources
        $this->resources = [];
        $filePaths = array_values($files);
        foreach ($filePaths as $path) {
            $this->resources[] = new FileResource($path);
        }

        if ($cached = $this->getCachedConfiguration($filePaths)) {
            return $cached;
        }

        // Load the configuration
        $configuration = $this->loader->getConfiguration();
        $configuration['meta'] = $filePaths;

        // Cache the configuration
        $this->cache->write($configuration, $this->resources);

        return $configuration;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// CACHING ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param string[] $filePaths
     *
     * @return array
     */
    protected function getCachedConfiguration($filePaths)
    {
        // Return cached version if available
        if (!$this->cache->isFresh()) {
            return;
        }

        // If the files the configuration was cached from
        // match the ones we have, return the cache
        $cache = $this->cache->getContents();
        if (Arr::get($cache, 'meta') === $filePaths) {
            return $cache;
        }
    }
}
