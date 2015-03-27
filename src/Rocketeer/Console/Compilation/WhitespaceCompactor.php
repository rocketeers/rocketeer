<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Console\Compilation;

use Herrera\Box\Compactor\Php;

/**
 * Strips out whitespace in the Rocketeer file
 * before the PHAR compilation.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class WhitespaceCompactor extends Php
{
    /**
     * Checks whether a given file is to be stripped or not
     * Configuration file are left as is.
     *
     * @param string $file
     *
     * @return bool
     */
    public function supports($file)
    {
        return dirname($file) !== 'src/config' && parent::supports($file);
    }
}
