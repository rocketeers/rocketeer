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

/**
 * Provides methods to manage a Bower application.
 */
class Bower extends AbstractPackageManager
{
    /**
     * The name of the manifest file to look for.
     *
     * @var string
     */
    protected $manifest = 'bower.json';

    /**
     * Get an array of default paths to look for.
     *
     * @return string[]
     */
    protected function getKnownPaths()
    {
        return [
            'bower',
            $this->releasesManager->getCurrentReleasePath().'/node_modules/.bin/bower',
        ];
    }

    /**
     * Get where dependencies are installed.
     *
     * @return string
     */
    public function getDependenciesFolder()
    {
        // Look for a configuration file
        $paths = array_filter([
            $this->paths->getBasePath().'.bowerrc',
            $this->paths->getUserHomeFolder().'/.bowerrc',
        ], [$this->files, 'has']);

        $file = head($paths);
        if ($file) {
            $file = $this->files->read($file);
            $file = json_decode($file, true);
        }

        return array_get($file, 'directory', 'bower_components');
    }
}
