<?php
namespace Rocketeer\Services\Filesystem;

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
        return 'include';
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
