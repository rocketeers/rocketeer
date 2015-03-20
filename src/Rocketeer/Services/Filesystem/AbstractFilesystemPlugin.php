<?php
namespace Rocketeer\Services\Filesystem;

use League\Flysystem\Plugin\AbstractPlugin;

abstract class AbstractFilesystemPlugin extends AbstractPlugin
{
    /**
     * @type string
     */
    protected $function;

    /**
     * @param string|null $path
     *
     * @return bool
     */
    public function handle($path = null)
    {
        $path     = $this->filesystem->getAdapter()->applyPathPrefix($path);
        $function = $this->function;

        return $function($path);
    }
}
