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

namespace Rocketeer\Services\Connections\Credentials\Keys;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Represents the credentials to something in
 * a way that can be easily serialized.
 */
abstract class AbstractKey implements Arrayable
{
    /**
     * @var string[]
     */
    protected $arrayable = [];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Get the components to compute the handle from.
     *
     * @return string[]
     */
    protected function getAttributes()
    {
        $components = [];
        foreach ($this->arrayable as $attribute) {
            $components[] = $this->$attribute;
        }

        return $components;
    }

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
        return $this->computeHandle($this->getAttributes());
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

    ////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////// SERIALIZATION /////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toHandle();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->getAttributes();
    }
}
