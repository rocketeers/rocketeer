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

namespace Rocketeer\Services\Storages;

use Cache\Adapter\Common\CacheItem;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use League\Flysystem\FilesystemInterface;
use Rocketeer\Services\Container\Container;
use Rocketeer\Traits\ContainerAwareTrait;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Provides and persists informations in a filesystem.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Storage
{
    /**
     * @var string
     */
    protected $filesystem;

    /**
     * @var CacheItemPoolInterface
     */
    protected $cache;

    /**
     * @param FilesystemInterface|string $filesystem
     * @param string                     $folder
     */
    public function __construct(FilesystemInterface $filesystem, $folder)
    {
        $this->filesystem = $filesystem;
        $this->cache = new FilesystemCachePool($filesystem);
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// VALUES ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get a value in storage.
     *
     * @param string                     $key
     * @param array|string|callable|null $fallback
     *
     * @return mixed
     */
    public function get($key, $fallback = null)
    {
        return $this->cache->getItem($key)->get() ?: $fallback;
    }

    /**
     * Set a value in storage.
     *
     * @param string|array $key
     * @param mixed|null   $value
     */
    public function set($key, $value = null)
    {
        // Recursive set
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }

            return;
        }

        $item = new CacheItem($key);
        $item->set($value);

        $this->cache->save($item);
    }

    /**
     * Forget a value from storage.
     *
     * @param string $key
     */
    public function forget($key)
    {
        $this->cache->deleteItem($key);
    }

    /**
     * Destroy the storage file.
     *
     * @return bool
     */
    public function destroy()
    {
        if ($this->filesystem->has('cache')) {
            return $this->cache->clear();
        }
    }
}
