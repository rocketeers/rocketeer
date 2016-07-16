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

namespace Rocketeer\Services\Filesystem;

use League\Flysystem\FilesystemInterface;

interface LocalFilesystemInterface extends FilesystemInterface
{
    /**
     * @param string $path
     *
     * @return bool
     */
    public function isDirectory($path);
}
