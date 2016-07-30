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

namespace Rocketeer\Services\Filesystem\Plugins;

use League\Flysystem\Plugin\AbstractPlugin;

/**
 * Copy a directory somewhere.
 */
class CopyDirectoryPlugin extends AbstractPlugin
{
    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'copyDir';
    }

    /**
     * @param string $from
     * @param string $to
     * @param bool   $overwrite
     */
    public function handle($from, $to, $overwrite = false)
    {
        foreach ($this->filesystem->listContents($from) as $file) {
            $newPath = $to.'/'.$file['basename'];
            if ($this->filesystem->has($newPath) && !$overwrite) {
                continue;
            }

            $this->filesystem->copy($file['path'], $newPath);
        }
    }
}
