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

/**
 * Interface for a standard Filesystem with the Rocketeer plugins
 * registered on it.
 */
interface FilesystemInterface extends \League\Flysystem\FilesystemInterface
{
    /**
     * @param string $path
     *
     * @return bool
     */
    public function isDirectory($path);

    /**
     * @param string $filepath
     * @param string $appended
     *
     * @return bool
     */
    public function append($filepath, $appended);

    /**
     * @param string $path
     *
     * @return bool
     */
    public function includeFile($path);

    /**
     * @param string $from
     * @param string $to
     * @param bool   $overwrite
     */
    public function copyDir($from, $to, $overwrite = false);

    /**
     * @param string $path
     * @param string $newpath
     */
    public function forceCopy($path, $newpath);
}
