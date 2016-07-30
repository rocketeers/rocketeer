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
 * Creates a file with some content or append to it
 * if it exists.
 */
class AppendPlugin extends AbstractPlugin
{
    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'append';
    }

    /**
     * @param string $filepath
     * @param string $appended
     *
     * @return bool
     */
    public function handle($filepath, $appended)
    {
        if ($this->filesystem->has($filepath)) {
            $contents = $this->filesystem->read($filepath);

            return $this->filesystem->update($filepath, $contents.$appended);
        }

        return $this->filesystem->put($filepath, $appended);
    }
}
