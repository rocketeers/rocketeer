<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Abstracts;

abstract class AbstractPackageManager extends AbstractBinary
{
    /**
     * The name of the manifest file to look for.
     *
     * @type string
     */
    protected $manifest;

    /**
     * Whether the manager is enabled or not.
     *
     * @return bool
     */
    public function isExecutable()
    {
        return $this->getBinary() && $this->hasManifest();
    }

    /**
     * Check if the manifest file exists, locally or on server.
     *
     * @return bool
     */
    public function hasManifest()
    {
        $server = $this->paths->getFolder('current/'.$this->manifest);
        $server = $this->bash->fileExists($server);

        $local = $this->app['path.base'].DS.$this->manifest;
        $local = $this->files->exists($local);

        return $local || $server;
    }

    /**
     * Get the contents of the manifest file.
     *
     * @throws \Illuminate\Filesystem\FileNotFoundException
     *
     * @return string|null
     */
    public function getManifestContents()
    {
        $manifest = $this->app['path.base'].DS.$this->manifest;
        if ($this->files->exists($manifest)) {
            return $this->files->get($manifest);
        }

        return;
    }

    /**
     * @return string
     */
    public function getManifest()
    {
        return $this->manifest;
    }
}
