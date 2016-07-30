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

namespace Rocketeer\Services\Config\Files\Loaders;

use Symfony\Component\Finder\SplFileInfo;

/**
 * Interface for a class that can load the configuration
 * from a specified set of folders.
 */
interface ConfigurationLoaderInterface
{
    /**
     * @param string[] $folders
     */
    public function setFolders(array $folders);

    /**
     * @return string[]
     */
    public function getFolders();

    /**
     * @return SplFileInfo[]
     */
    public function getFiles();

    /**
     * Get a final merged version of the configuration,
     * taking into account defaults, user and contextual
     * configurations.
     *
     * @return array
     */
    public function getConfiguration();
}
