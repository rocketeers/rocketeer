<?php
namespace Rocketeer\Services\Filesystem\Plugins;

use League\Flysystem\Plugin\AbstractPlugin;

class AppendPlugin extends AbstractPlugin
{
    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'append';
    }

    /**
     * @param string $filepath
     * @param string $appended
     *
     * @return bool
     */
    public function handle($filepath, $appended)
    {
        if ($this->filesystem->has($filepath)) {
            $contents = $this->filesystem->read($filepath);

            return $this->filesystem->update($filepath, $contents.$appended);
        }

        return $this->filesystem->put($filepath, $appended);
    }
}
