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

namespace Rocketeer\Services\Config\Files\Finders;

use League\Flysystem\FilesystemInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * An abstract for classes responsible for finding configuration files.
 */
abstract class AbstractConfigurationFinder
{
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $folder;

    /**
     * @param FilesystemInterface $filesystem
     * @param string              $folder
     */
    public function __construct(FilesystemInterface $filesystem, $folder)
    {
        $this->filesystem = $filesystem;
        $this->folder = $folder;
    }

    /**
     * @return SplFileInfo[]
     */
    abstract public function getFiles();

    ////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////// HELPERS ////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * @param string|string[] $folders
     *
     * @return Finder|SplFileInfo[]
     */
    protected function getFinder($folders)
    {
        return $this->getFinderForFolders($folders)->notName('/(events|tasks)\.php/')->sortByName();
    }

    /**
     * @param string[] $folders
     *
     * @return Finder
     */
    protected function getFinderForFolders($folders)
    {
        $folders = array_map([$this->filesystem->getAdapter(), 'applyPathPrefix'], (array) $folders);

        return (new Finder())->in($folders);
    }
}
