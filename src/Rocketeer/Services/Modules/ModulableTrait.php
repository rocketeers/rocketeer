<?php
namespace Rocketeer\Services\Modules;

use League\Container\ContainerAwareInterface;

trait ModulableTrait
{
    /**
     * @var AbstractModule[]
     */
    protected $registered;

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        foreach ($this->registered as $method => $module) {
            if ($method === $name) {
                $module = $this->registered[$method];
                $module->setModulable($this);
                if ($module instanceof ContainerAwareInterface && $this instanceof ContainerAwareInterface) {
                    $module->setContainer($this->getContainer());
                }

                return $module->$method(...$arguments);
            }
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
