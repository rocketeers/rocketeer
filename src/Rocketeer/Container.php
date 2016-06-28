<?php

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
        unset($this->definitions[$key]);
        unset($this->shared[$key]);
        unset($this->sharedDefinitions[$key]);
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
