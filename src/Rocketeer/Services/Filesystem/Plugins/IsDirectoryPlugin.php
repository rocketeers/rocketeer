<?php
namespace Rocketeer\Services\Filesystem\Plugins;

use League\Flysystem\Plugin\AbstractPlugin;

class IsDirectoryPlugin extends AbstractPlugin
{
    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'isDirectory';
    }

    /**
     * @param string|null $path
     *
     * @return bool
     */
    public function handle($path = null)
    {
        $path = $this->filesystem->getAdapter()->applyPathPrefix($path);

        return is_dir($path);
    }
}
