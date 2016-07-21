<?php
namespace Rocketeer\Services\Filesystem\Plugins;

use League\Flysystem\Plugin\AbstractPlugin;

class CopyDirectoryPlugin extends AbstractPlugin
{
    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'copyDir';
    }

    /**
     * @param string $from
     * @param string $to
     * @param bool   $overwrite
     */
    public function handle($from, $to, $overwrite = false)
    {
        foreach ($this->filesystem->listContents($from) as $file) {
            $newPath = $to.'/'.$file['basename'];
            if ($this->filesystem->has($newPath) && !$overwrite) {
                continue;
            }

            $this->filesystem->copy($file['path'], $newPath);
        }
    }
}
