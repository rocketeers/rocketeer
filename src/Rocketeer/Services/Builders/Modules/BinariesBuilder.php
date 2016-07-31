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

namespace Rocketeer\Services\Builders\Modules;

use Rocketeer\Binaries\AnonymousBinary;

/**
 * Builds binaries and package managers.
 */
class BinariesBuilder extends AbstractBuilderModule
{
    /**
     * Build a binary.
     *
     * @param string $binary
     *
     * @return \Rocketeer\Binaries\AbstractBinary|\Rocketeer\Binaries\PackageManagers\AbstractPackageManager
     */
    public function buildBinary($binary)
    {
        $class = $this->modulable->findQualifiedName($binary, 'binaries');

        // If there is a class by that name
        if ($class) {
            return new $class($this->container);
        }

        // Else wrap the command in an AnonymousBinary
        $anonymous = new AnonymousBinary($this->container);
        $anonymous->setBinary($this->bash->which($binary));

        return $anonymous;
    }

    /**
     * @return string[]
     */
    public function getProvided()
    {
        return [
            'buildBinary',
        ];
    }
}
