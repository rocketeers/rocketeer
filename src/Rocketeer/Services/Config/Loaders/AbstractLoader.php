<?php
namespace Rocketeer\Services\Config\Loaders;

use InvalidArgumentException;
use Symfony\Component\Config\Loader\Loader;

abstract class AbstractLoader extends Loader
{
    /**
     * @var string
     */
    protected $extension;

    /**
     * @param string $file
     *
     * @return array
     */
    abstract protected function parse($file);

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        if (!is_file($resource) || !is_readable($resource)) {
            throw new InvalidArgumentException(sprintf('File "%s" is not a regular file.', $resource));
        }

        $parsed = $this->parse($resource);
        if (!is_array($parsed)) {
            throw new InvalidArgumentException(sprintf('Could not parse %s of file "%s"', strtoupper($this->extension), $resource));
        }

        return $parsed;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && pathinfo($resource, PATHINFO_EXTENSION) === $this->extension;
    }
}
