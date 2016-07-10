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
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (array_key_exists($name, $this->registered)) {
            $module = $this->registered[$name];
            $module->setModulable($this);
            if ($module instanceof ContainerAwareInterface && $this instanceof ContainerAwareInterface) {
                $module->setContainer($this->getContainer());
            }

            return $module->$name(...$arguments);
        }

        throw new ModuleNotFoundException($name, __CLASS__);
    }

    /**
     * @param AbstractModule $module
     */
    public function register(AbstractModule $module)
    {
        foreach ($module->getProvided() as $method) {
            $this->registered[$method] = $module;
        }
    }
}
