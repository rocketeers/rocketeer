<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Services\Config\Loaders;

use Symfony\Component\Yaml\Yaml;

/**
 * Parses and load a YAML file.
 */
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
