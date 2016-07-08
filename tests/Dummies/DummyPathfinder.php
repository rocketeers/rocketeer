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

namespace Rocketeer\Dummies;

use Rocketeer\Services\Environment\Pathfinders\PathfinderInterface;

class DummyPathfinder implements PathfinderInterface
{
    /**
     * @param string $foo
     *
     * @return string
     */
    public function foobar($foo)
    {
        return $foo.'foo';
    }

    /**
     * The methods this pathfinder provides.
     *
     * @return string[]
     */
    public function provides()
    {
        return ['foobar'];
    }
}
