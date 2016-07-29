<?php
namespace Rocketeer\Services\Config\Loaders;

class PhpLoader extends AbstractLoader
{
    /**
     * @var string
     */
    protected $extension = 'php';

    /**
     * @param string $file
     *
     * @return array
     */
    protected function parse($file)
    {
        return include $file;
    }
}
