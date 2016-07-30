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
 * Puts contents into a file if it exists, if not create it.
 */
class UpsertPlugin extends AbstractPlugin
{
    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'upsert';
    }

    /**
     * @param string $filename
     * @param string $contents
     *
     * @return bool
     */
    public function handle($filename, $contents)
    {
        return $this->filesystem->has($filename) ? $this->filesystem->update($filename, $contents) : $this->filesystem->write($filename, $contents);
    }
}
