<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Filesystem\Plugins;

use League\Flysystem\Plugin\AbstractPlugin;

class IsDirectoryPlugin extends AbstractPlugin
{
    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'isDirectory';
    }

    /**
     * @param string|null $path
     *
     * @return bool
     */
    public function handle($path = null)
    {
        $path = $this->filesystem->getAdapter()->applyPathPrefix($path);

        return is_dir($path);
    }
}
