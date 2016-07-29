<?php
namespace Rocketeer\Services\Config\Loaders;

use Symfony\Component\Yaml\Yaml;

class YamlLoader extends AbstractLoader
{
    /**
     * @var string
     */
    protected $extension = 'yml';

    /**
     * {@inheritdoc}
     */
    protected function parse($file)
    {
        $content = file_get_contents($file);
        $content = Yaml::parse($content);

        return $content;
    }
}
