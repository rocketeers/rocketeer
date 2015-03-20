<?php
namespace Rocketeer\Services\Filesystem;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\PluginInterface;

class IsDirectoryPlugin extends AbstractFilesystemPlugin
{
    /**
     * @type string
     */
    protected $function = 'is_dir';

    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'isDirectory';
    }
}
