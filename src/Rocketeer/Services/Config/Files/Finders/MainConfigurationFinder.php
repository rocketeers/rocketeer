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
 * Find the main configuration files if not consolidated.
 */
class MainConfigurationFinder extends AbstractConfigurationFinder
{
    /**
     * @return SplFileInfo[]
     */
    public function getFiles()
    {
        $files = $this->getFinder($this->folder)->exclude(['connections', 'stages', 'plugins'])->files();
        $files = array_values(iterator_to_array($files));

        return $files;
    }
}
