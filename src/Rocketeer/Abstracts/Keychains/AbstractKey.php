<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Abstracts\Keychains;

use Illuminate\Support\Fluent;

abstract class AbstractKey extends Fluent
{
    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HANDLES ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Compute a simplified string version of the key.
     *
     * @return string
     */
    public function toHandle()
    {
        return $this->computeHandle($this->getHandleComponents());
    }

    /**
     * Get the components to compute the handle from.
     *
     * @return string[]
     */
    protected function getHandleComponents()
    {
        return $this->attributes;
    }

    /**
     * Compute a handle from components.
     *
     * @param string[] $components
     *
     * @return string
     */
    protected function computeHandle(array $components)
    {
        return implode('/', $components);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toHandle();
    }
}
