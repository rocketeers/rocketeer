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

use Illuminate\Container\Container;
use Rocketeer\Abstracts\AbstractStorage;
use Rocketeer\Interfaces\StorageInterface;

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
     * @type string
     */
    protected $hash;

    /**
     * The folder where the file resides.
     *
     * @type string
     */
    protected $folder;

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
            $this->files->makeDirectory($folder, 0755, false, true);
        }

        // Set path to storage folder
        $this->folder = $folder ?: $this->app['path.storage'].DS.'meta';

        // Flush if necessary
        if ($this->shouldFlush()) {
            $this->destroy();
        }

        $this->set('hash', $this->getHash());
    }

    /**
     * Delegate methods to Environment for BC.
     *
     * @todo Remove in 3.0
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->environment, $name], $arguments);
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// SALTS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get the current salt in use.
     *
     * @return string
     */
    public function getHash()
    {
        // Return cached hash if any
        if ($this->hash) {
            return $this->hash;
        }

        // Get the contents of the configuration folder
        $salt   = '';
        $folder = $this->paths->getConfigurationPath();
        $files  = (array) $this->files->glob($folder.'/*.php');

        // Remove custom files and folders
        foreach (['events', 'tasks'] as $handle) {
            $path  = $this->app['path.rocketeer.'.$handle];
            $index = array_search($path, $files, true);
            if ($index !== false) {
                unset($files[$index]);
            }
        }

        // Compute the salts
        foreach ($files as $file) {
            $file = $this->files->getRequire($file);
            $salt .= json_encode($file);
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
        // Cancel if the file doesn't exist
        if (!$this->files->exists($this->getFilepath())) {
            return [];
        }

        // Get and parse file
        $contents = $this->files->get($this->getFilepath());
        $contents = json_decode($contents, true);

        return $contents;
    }

    /**
     * Save the contents of a file.
     *
     * @param array $contents
     */
    protected function saveContents($contents)
    {
        // Yup. Don't look at me like that.
        @$this->files->put($this->getFilepath(), json_encode($contents));
    }

    /**
     * Destroy the file.
     *
     * @return bool
     */
    public function destroy()
    {
        return $this->files->delete($this->getFilepath());
    }
}
