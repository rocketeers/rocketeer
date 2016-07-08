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

namespace Rocketeer\Binaries;

class Phpunit extends AbstractBinary
{
    /**
     * Get an array of default paths to look for.
     *
     * @return string[]
     */
    protected function getKnownPaths()
    {
        return [
            'phpunit',
            $this->releasesManager->getCurrentReleasePath().'/vendor/bin/phpunit',
        ];
    }
}
