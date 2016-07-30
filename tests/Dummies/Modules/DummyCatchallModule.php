<?php
namespace Rocketeer\Dummies\Modules;

use Rocketeer\Services\Modules\AbstractModule;

class DummyCatchallModule extends AbstractModule
{

    protected $default = true;

    function __call($name, $arguments)
    {
        return $name;
    }

    public function getProvided()
    {
        return [];
    }
}
