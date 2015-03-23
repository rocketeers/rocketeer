<?php
namespace Rocketeer\Services\Filesystem\Plugins;

use League\Flysystem\Plugin\AbstractPlugin;

class UpsertPlugin extends AbstractPlugin
{
    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'upsert';
    }

    /**
     * @param string $filename
     * @param string $contents
     *
     * @return boolean
     */
    public function handle($filename, $contents)
    {
        return $this->filesystem->has($filename) ? $this->filesystem->update($filename, $contents) : $this->filesystem->write($filename, $contents);
    }
}
