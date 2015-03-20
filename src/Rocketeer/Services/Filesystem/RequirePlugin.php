<?php
namespace Rocketeer\Services\Filesystem;

use League\Flysystem\Plugin\AbstractPlugin;

class RequirePlugin extends AbstractPlugin
{
    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'readRequire';
    }

    /**
     * @param string|null $path
     *
     * @return bool
     */
    public function handle($path = null)
    {
        $path = $this->filesystem->getAdapter()->applyPathPrefix($path);

        return require $path;
    }
}
