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

use Rocketeer\Binaries\Php;

/**
 * Provides methods to manage a Composer application.
 */
class Composer extends AbstractPackageManager
{
    /**
     * The name of the manifest file to look for.
     *
     * @var string
     */
    protected $manifest = 'composer.json';

    /**
     * Get an array of default paths to look for.
     *
     * @return string[]
     */
    protected function getKnownPaths()
    {
        return [
            'composer',
            $this->paths->getHomeFolder().'/composer.phar',
            $this->releasesManager->getCurrentReleasePath().'/composer.phar',
        ];
    }

    /**
     * Change Composer's binary.
     *
     * @param string $binary
     */
    public function setBinary($binary)
    {
        parent::setBinary($binary);

        // Prepend PHP command if executing from archive
        if (mb_strpos($binary, 'composer.phar') !== false) {
            $php = new Php($this->container);
            $this->setParent($php);
        }
    }

    /**
     * Get where dependencies are installed.
     *
     * @return string
     */
    public function getDependenciesFolder()
    {
        return 'vendor';
    }
}
