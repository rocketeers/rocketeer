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
     * The name of the manifest file to look for
     *
     * @type string
     */
    protected $manifest;

    /**
     * Whether the manager is enabled or not
     *
     * @return boolean
     */
    public function isExecutable()
    {
        return $this->getBinary() && $this->hasManifest();
    }

    /**
     * Check if the manifest file exists, locally or on server
     *
     * @return bool
     */
    public function hasManifest()
    {
        $server = $this->paths->getFolder('current/'.$this->manifest);
        $server = $this->bash->fileExists($server);

        $local = $this->getManifestPath();
        $local = $this->files->exists($local);

        return $local || $server;
    }

    /**
     * Get the contents of the manifest file
     *
     * @return string|null
     * @throws \Illuminate\Filesystem\FileNotFoundException
     */
    public function getManifestContents()
    {
        $manifest = $this->getManifestPath();
        if ($this->files->exists($manifest)) {
            return $this->files->get($manifest);
        }

        return;
    }

    /**
     * Get the name of the manifest file
     *
     * @return string
     */
    public function getManifest()
    {
        return $this->manifest;
    }

    /**
     * Get the path to the manifest file
     *
     * @return string
     */
    public function getManifestPath()
    {
        return $this->paths->getApplicationPath().$this->manifest;
    }

    /**
     * Get where dependencies are installed
     *
     * @return string|null
     */
    abstract public function getDependenciesFolder();
}
