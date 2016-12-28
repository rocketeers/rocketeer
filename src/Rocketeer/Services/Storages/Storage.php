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

use Exception;
use Illuminate\Support\Arr;
use League\Flysystem\FilesystemInterface;
use Rocketeer\Services\Container\Container;
use Rocketeer\Traits\ContainerAwareTrait;

/**
 * Provides and persists informations in the local filesystem.
 */
class Storage
{
    use ContainerAwareTrait;

    /**
     * @var string
     */
    protected $filesystem;

    /**
     * The folder in which to store the storage file.
     *
     * @var string
     */
    protected $folder;

    /**
     * The name to use for the storage file.
     *
     * @var string
     */
    protected $filename = 'state';

    /**
     * A cache of the contents.
     *
     * @var array
     */
    protected $contents;

    /**
     * Build a new ServerStorage.
     *
     * @param Container $container
     * @param string    $filesystem
     * @param string    $folder
     * @param string    $filename
     */
    public function __construct(Container $container, $filesystem, $folder, $filename)
    {
        $this->container = $container;
        $this->filesystem = $filesystem;
        $this->folder = $folder;
        $this->setFilename($filename);
    }

    /**
     * @param string $folder
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = mb_strtolower(basename($filename, '.json'));
    }

    /**
     * @return FilesystemInterface
     */
    public function getFilesystem()
    {
        if ($this->rocketeer->isLocal() || $this->filesystem === 'local') {
            return $this->files;
        }

        return $this->filesystems->getFilesystem($this->filesystem);
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename.'.json';
    }

    /**
     * @return string
     */
    public function getFilepath()
    {
        return $this->folder.DS.$this->getFilename();
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// VALUES ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get a value in storage.
     *
     * @param string|null                $key
     * @param array|string|callable|null $fallback
     *
     * @return string|int|array
     */
    public function get($key = null, $fallback = null)
    {
        $contents = $this->getContents();

        return Arr::get($contents, $key, $fallback);
    }

    /**
     * Set a value in storage.
     *
     * @param string|array $key
     * @param mixed|null   $value
     */
    public function set($key, $value = null)
    {
        // Set the value on the contents
        $contents = (array) $this->getContents();
        if (is_array($key)) {
            $contents = $key;
        } else {
            Arr::set($contents, $key, $value);
        }

        $this->saveContents($contents);
    }

    /**
     * Forget a value from storage.
     *
     * @param string $key
     */
    public function forget($key)
    {
        $contents = $this->getContents();
        Arr::forget($contents, $key);

        $this->saveContents($contents);
    }

    /**
     * Get the full contents of the storage file.
     *
     * @return array
     */
    protected function getContents()
    {
        // Cancel if the file doesn't exist
        if (!$this->getFilesystem()->has($this->getFilepath())) {
            return [];
        }

        // Get and parse file
        if ($this->contents === null) {
            $this->contents = $this->getFilesystem()->read($this->getFilepath());
            $this->contents = json_decode($this->contents, true);
        }

        return (array) $this->contents;
    }

    /**
     * Save the contents of the storage file.
     *
     * @param array $contents
     */
    protected function saveContents($contents)
    {
        $this->contents = $contents;

        try {
            $this->getFilesystem()->put($this->getFilepath(), json_encode($contents));
        } catch (Exception $e) {
            // ...
        }
    }

    /**
     * Destroy the storage file.
     *
     * @return bool
     */
    public function destroy()
    {
        $this->contents = [];
        $filepath = $this->getFilepath();

        if ($this->getFilesystem()->has($filepath)) {
            return $this->getFilesystem()->delete($filepath);
        }
    }
}
