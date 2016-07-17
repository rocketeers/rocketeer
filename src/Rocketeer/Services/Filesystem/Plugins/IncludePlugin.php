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

class IncludePlugin extends AbstractPlugin
{
    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'includeFile';
    }

    /**
     * @param string|null $path
     *
     * @return bool
     */
    public function handle($path)
    {
        $path = $this->filesystem->getAdapter()->applyPathPrefix($path);

        return include $path;
    }
}
