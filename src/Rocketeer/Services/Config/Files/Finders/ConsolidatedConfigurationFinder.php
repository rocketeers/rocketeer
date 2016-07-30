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

use Symfony\Component\Finder\SplFileInfo;

/**
 * Finds a consolidated configuration file (rocketeer.xxx).
 */
class ConsolidatedConfigurationFinder extends AbstractConfigurationFinder
{
    /**
     * @return SplFileInfo[]
     */
    public function getFiles()
    {
        $files = $this->getFinder($this->folder)->name('/^rocketeer\./')->files();
        $files = iterator_to_array($files);
        if (!$files) {
            return [];
        }

        return [head($files)];
    }
}
