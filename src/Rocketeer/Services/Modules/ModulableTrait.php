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

namespace Rocketeer\Services\Modules;

use League\Container\ContainerAwareInterface;

trait ModulableTrait
{
    /**
     * @var AbstractModule[]
     */
    protected $registered = [];

    /**
     * @return AbstractModule[]
     */
    public function getRegistered()
    {
        return $this->registered;
    }

    /**
     * @param AbstractModule $module
     */
    public function register(AbstractModule $module)
    {
        $provided = $module->getProvided();
        if ($module->isDefault()) {
            $provided[] = '__DEFAULT__';
        }

        // Setup module
        $module->setModulable($this);
        if ($this instanceof ContainerAwareInterface) {
            $module->setContainer($this->getContainer());
        }

        foreach ($provided as $method) {
            $this->registered[$method] = $module;
        }
    }

    ////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////// CALLS /////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * {@inheritdoc}
     */
    public function __call($name, $arguments)
    {
        return $this->onModule($name, $arguments);
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function onModule($name, $arguments)
    {
        // Look in registered modules
        $key = isset($this->registered[$name]) ? $name : '__DEFAULT__';

        if (!isset($this->registered[$key])) {
            throw new ModuleNotFoundException($name, __CLASS__);
        }

        return $this->registered[$key]->$name(...$arguments);
    }
}
