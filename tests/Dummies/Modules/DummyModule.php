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

class DummyModule extends AbstractModule
{
    public function foo()
    {
        return 'foobar';
    }

    public function parent()
    {
        return $this->modulable->parent();
    }

    /**
     * @return string[]
     */
    public function getProvided()
    {
        return ['foo'];
    }
}
