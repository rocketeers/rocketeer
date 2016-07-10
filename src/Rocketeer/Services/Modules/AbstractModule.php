<?php
namespace Rocketeer\Services\Modules;

abstract class AbstractModule
{
    /**
     * @var ModulableInterface
     */
    protected $modulable;

    /**
     * @return string[]
     */
    abstract public function getProvided();

    /**
     * @return ModulableInterface
     */
    public function getModulable()
    {
        return $this->modulable;
    }

    /**
     * @param ModulableInterface $modulable
     */
    public function setModulable($modulable)
    {
        $this->modulable = $modulable;
    }
}
