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
 * Parses and load a PHP file.
 */
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
