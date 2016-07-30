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
 * Finds configuration related to plugins.
 */
class PluginsConfigurationFinder extends AbstractConfigurationFinder
{
    /**
     * @return SplFileInfo[]
     */
    public function getFiles()
    {
        $plugins = $this->folder.'/plugins';
        if (!$this->filesystem->has($plugins)) {
            return [];
        }

        $found = [];
        $files = $this->getFinderForFolders($plugins)->files();
        foreach ($files as $file) {
            $key = preg_replace('#(.*)'.$this->folder.DS.'(.+)\.php#', '$2', $file->getPathname());
            $key = vsprintf('%s.config.%s', explode(DS, $key));

            $found[$key] = $file;
        }

        return $found;
    }
}
