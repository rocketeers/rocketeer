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

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Finds contextual configuration.
 */
class ContextualConfigurationFinder extends AbstractConfigurationFinder
{
    /**
     * @return SplFileInfo[]
     */
    public function getFiles()
    {
        $found = [];
        $contextual = $this->getFinderForFolders($this->folder)->name('/(stages|connections)/')->directories();
        foreach ($contextual as $type) {
            /** @var SplFileInfo[] $files */
            $files = (new Finder())->in($type->getPathname())->files();
            foreach ($files as $file) {
                $key = preg_replace('#(.*)'.$this->folder.DS.'(.+)\.php#', '$2', $file->getPathname());
                $key = vsprintf('config.on.%s.%s', explode(DS, $key));

                $found[$key] = $file;
            }
        }

        return $found;
    }
}
