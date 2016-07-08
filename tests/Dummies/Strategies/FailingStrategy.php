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

namespace Rocketeer\Dummies\Strategies;

class FailingStrategy extends \Rocketeer\Strategies\AbstractStrategy
{
    public function fire()
    {
        return false;
    }
}
