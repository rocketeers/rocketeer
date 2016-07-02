<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Storages;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Decorator to provide caching capabilities to
 * a storage class.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class CachedStorage
{
    /**
     * The storage class to wrap.
     *
     * @var Storage
     */
    protected $storage;

    /**
     * The folder to hash.
     *
     * @var string
     */
    protected $folder;

    /**
     * The hash of the folder.
     *
     * @var string
     */
    protected $hash;

    /**
     * @param Storage $storage
     * @param string  $folder
     */
    public function __construct(Storage $storage, $folder)
    {
        $this->storage = $storage;
        $this->folder = $folder;

        // Flush if necessary
        if ($this->shouldFlush()) {
            $this->storage->destroy();
        }

        $this->storage->set('hash', $this->getHash());
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        return $this->storage->$name(...$arguments);
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// SALTS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get the current salt in use.
     *
     * @return string|null
     */
    public function getHash()
    {
        // Return cached hash if any
        if ($this->hash) {
            return $this->hash;
        }

        // Get the contents of the configuration folder
        $salt = '';
        $filesystem = $this->storage->getFilesystem();
        if (!$filesystem->has($this->folder)) {
            return;
        }

        $finder = new Finder();
        $folder = $filesystem->getAdapter()->applyPathPrefix($this->folder);
        $files = $finder
            ->in($folder)
            ->name('*.php')
            ->exclude(['tasks', 'events', 'strategies'])
            ->notName('/(events|tasks)\.php/')
            ->files();

        // Sort by name
        $files = iterator_to_array($files);
        ksort($files);

        // Compute the salts
        /** @var SplFileInfo[] $files */
        foreach ($files as $file) {
            $file = $filesystem->getAdapter()->removePathPrefix($file);
            $contents = $filesystem->readRequire($file);
            $salt .= json_encode($contents);
        }

        // Cache it
        $this->hash = md5($salt);

        return $this->hash;
    }

    /**
     * Flushes the repository if required.
     *
     * @return bool
     */
    public function shouldFlush()
    {
        $currentHash = $this->storage->get('hash');

        return $currentHash && $currentHash !== $this->getHash();
    }
}
