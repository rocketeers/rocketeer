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

use Cache\Adapter\Filesystem\FilesystemCachePool;
use Illuminate\Support\Arr;
use League\Flysystem\FilesystemInterface;
use Rocketeer\Services\Container\Container;
use Rocketeer\Traits\ContainerAwareTrait;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Provides and persists informations in a filesystem.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Storage extends FilesystemCachePool
{
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @param FilesystemInterface|string $filesystem
     * @param string                     $folder
     */
    public function __construct(FilesystemInterface $filesystem, $folder)
    {
        // Set folder as path prefix
        $this->filesystem = $filesystem;

        parent::__construct($filesystem, $folder);
    }

    /**
     * @return FilesystemInterface
     */
    public function getFilesystem()
    {
        return $this->filesystem;
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
        if (strpos($key, '.') === false) {
            $value = $this->getItem($key)->get() ?: $fallback;
        } else {
            list ($item, $key) = $this->getRootItem($key);
            $value = Arr::get($item->get(), $key);
        }

        return $value ?: $fallback;
    }

    /**
     * Set a value in storage.
     *
     * @param string|array $key
     * @param mixed|null   $value
     */
    public function set($key, $value = null)
    {
        $values = is_array($key) ? $key : [$key => $value];
        foreach ($values as $key => $value) {
            list ($item, $newKey) = $this->getRootItem($key);
            if (strpos($key, '.') === false) {
                $this->save($item->set($value));
                continue;
            }

            $current = (array) $item->get();

            Arr::set($current, $newKey, $value);
            $this->save($item->set($current));
        }
    }

    /**
     * Forget a value from storage.
     *
     * @param string $key
     */
    public function forget($key)
    {
        $this->deleteItem($key);
    }

    ////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////// HELPERS ////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * @param string $key
     *
     * @return array
     */
    protected function getRootItem($key)
    {
        $keys = explode('.', $key);
        $root = $keys[0];
        $item = $this->getItem($root);

        return [$item, str_replace($item->getKey().'.', null, $key)];
    }
}
