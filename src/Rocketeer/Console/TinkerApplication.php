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

namespace Rocketeer\Console;

use Rocketeer\Traits\ContainerAwareTrait;

/**
 * A class exposing Rocketeer's classes as public
 * for use within a REPL.
 */
class TinkerApplication
{
    use ContainerAwareTrait;
}
