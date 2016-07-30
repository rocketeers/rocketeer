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

namespace Rocketeer\Binaries\PackageManagers;

use Rocketeer\Binaries\AbstractBinary;

/**
 * An abstract for classes representing a package manager.
 */
abstract class AbstractPackageManager extends AbstractBinary
{
    /**
     * The name of the manifest file to look for.
     *
     * @var string
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
        $server = $this->releasesManager->getCurrentReleasePath($this->manifest);
        $server = $this->bash->fileExists($server);

        $local = $this->getManifestPath();
        $local = $this->files->has($local);

        return $local || $server;
    }

    /**
     * Get the contents of the manifest file.
     *
     * @return false|string|null
     */
    public function getManifestContents()
    {
        $manifest = $this->getManifestPath();
        if ($this->files->has($manifest)) {
            return $this->files->read($manifest);
        }
    }

    /**
     * Get the name of the manifest file.
     *
     * @return string
     */
    public function getManifest()
    {
        return $this->manifest;
    }

    /**
     * Get the path to the manifest file.
     *
     * @return string
     */
    public function getManifestPath()
    {
        return $this->paths->getBasePath().$this->manifest;
    }

    /**
     * Get where dependencies are installed.
     *
     * @return string|null
     */
    abstract public function getDependenciesFolder();
}
