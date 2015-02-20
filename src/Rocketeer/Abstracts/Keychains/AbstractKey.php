<?php
namespace Rocketeer\Abstracts\Keychains;

use Illuminate\Support\Fluent;

abstract class AbstractKey extends Fluent
{
    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HANDLES ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Compute a simplified string version of the key
     *
     * @return string
     */
    public function toHandle()
    {
        return $this->computeHandle($this->getHandleComponents());
    }

    /**
     * Get the components to compute the handle from
     *
     * @return string[]
     */
    protected function getHandleComponents()
    {
        return $this->attributes;
    }

    /**
     * Compute a handle from components
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
