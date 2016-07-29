<?php
namespace Rocketeer\Services\Config\Loaders;

class JsonLoader extends AbstractLoader
{
    /**
     * @var string
     */
    protected $extension = 'json';

    /**
     * {@inheritdoc}
     */
    protected function parse($file)
    {
        $content = file_get_contents($file);
        $content = json_decode($content, true);

        return $content;
    }
}
