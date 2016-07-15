<?php
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
