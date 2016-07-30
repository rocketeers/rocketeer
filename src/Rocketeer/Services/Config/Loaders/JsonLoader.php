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

/**
 * Parses and load a JSON file.
 */
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
