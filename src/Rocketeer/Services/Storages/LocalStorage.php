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

use Exception;
use Illuminate\Contracts\Container\Container;
use Rocketeer\Abstracts\AbstractStorage;
use Rocketeer\Interfaces\StorageInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Provides and persists informations in local.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class LocalStorage extends AbstractStorage implements StorageInterface
{
    /**
     * The current hash in use.
     *
     * @var string
     */
    protected $hash;

    /**
     * The folder where the file resides.
     *
     * @var string
     */
    protected $folder;

    /**
     * A cache of the contents.
     *
     * @var array
     */
    protected $contents;

    /**
     * Build a new LocalStorage.
     *
     * @param Container   $app
     * @param string      $file
     * @param string|null $folder
     */
    public function __construct(Container $app, $file = 'deployments', $folder = null)
    {
        parent::__construct($app, $file);

        // Create personal storage if necessary
        if (!$this->app->bound('path.storage')) {
            $folder = $this->paths->getRocketeerConfigFolder();
            $this->files->createDir($folder);
        }

        // Set path to storage folder
        $this->folder = $folder ?: $this->app['path.storage'].DS.'meta';

        // Flush if necessary
        if ($this->shouldFlush()) {
            $this->destroy();
        }

        $this->set('hash', $this->getHash());
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
        $folder = $this->paths->getConfigurationPath();
        if (!$this->files->isDirectory($folder)) {
            return;
        }

        $finder = new Finder();
        $folder = $this->files->getAdapter()->applyPathPrefix($folder);
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
            $file = $this->files->getAdapter()->removePathPrefix($file);
            $contents = $this->files->readRequire($file);
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
        $currentHash = $this->get('hash');

        return $currentHash && $currentHash !== $this->getHash();
    }

    ////////////////////////////////////////////////////////////////////
    ////////////////////////// REPOSITORY FILE /////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Change the folder in use.
     *
     * @param string $folder
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;
    }

    /**
     * @return string
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Get the full path to the file.
     *
     * @return string
     */
    public function getFilepath()
    {
        return $this->folder.'/'.$this->file.'.json';
    }

    /**
     * Get the contents of a file.
     *
     * @return array
     */
    protected function getContents()
    {
        $filepath = $this->getFilepath();

        // Cancel if the file doesn't exist
        if (!$this->files->has($filepath)) {
            return [];
        }

        // Get and parse file
        if ($this->contents === null) {
            $this->contents = $this->files->read($filepath);
            $this->contents = json_decode($this->contents, true);
        }

        return $this->contents;
    }

    /**
     * Save the contents of a file.
     *
     * @param array $contents
     */
    protected function saveContents($contents)
    {
        $this->contents = $contents;

        try {
            $this->files->put($this->getFilepath(), json_encode($contents));
        } catch (Exception $e) {
            // ...
        }
    }

    /**
     * Destroy the file.
     *
     * @return bool
     */
    public function destroy()
    {
        $this->contents = [];

        return $this->files->delete($this->getFilepath());
    }
}
