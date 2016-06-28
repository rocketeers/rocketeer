<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer;

use League\Container\ReflectionContainer;

class Container extends \League\Container\Container
{
    /**
     * {@inheritdoc}
     */
    public function __construct($providers = null, $inflectors = null, $definitionFactory = null)
    {
        parent::__construct($providers, $inflectors, $definitionFactory);

        $this->delegate(new ReflectionContainer());
    }

    /**
     * @param string $key
     */
    public function remove($key)
    {
        unset($this->definitions[$key], $this->shared[$key], $this->sharedDefinitions[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function add($alias, $concrete = null, $share = false)
    {
        $this->remove($alias);

        return parent::add($alias, $concrete, $share);
    }
}
