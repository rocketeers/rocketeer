<?php

namespace Rocketeer\Services\Modules;

interface ModulableInterface
{
    /**
     * @param AbstractModule $module
     */
    public function register(AbstractModule $module);
}
