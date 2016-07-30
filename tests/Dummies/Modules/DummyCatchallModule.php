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

namespace Rocketeer\Dummies\Modules;

use Rocketeer\Services\Modules\AbstractModule;

class DummyCatchallModule extends AbstractModule
{
    protected $default = true;

    public function __call($name, $arguments)
    {
        return $name;
    }

    public function getProvided()
    {
        return [];
    }
}
