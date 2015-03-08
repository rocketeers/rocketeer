<?php
namespace Rocketeer\Services\Config;

use Symfony\Component\Config\ConfigCache;

class ConfigurationCache extends ConfigCache
{
    /**
     * Writes cache.
     *
     * @param array                                              $content  The content to write in the cache
     * @param \Symfony\Component\Config\ResourceInterface[]|null $metadata An array of ResourceInterface instances
     *
     * @throws \RuntimeException When cache file can't be written
     */
    public function write($content, array $metadata = null)
    {
        parent::write(serialize($content), $metadata);
    }

    /**
     * Flush the cache
     */
    public function flush()
    {
        @unlink($this->getFile());
    }

    /**
     * Get the contents of the cache
     *
     * @return array
     */
    public function getContents()
    {
        $file = $this->getFile();

        // Get an unserialize
        $configuration = file_get_contents($file);
        $configuration = unserialize($configuration);

        return $configuration;
    }

    /**
     * Get the path to the cache file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->__toString();
    }
}
