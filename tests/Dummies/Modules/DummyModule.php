<?php
namespace Rocketeer\Dummies\Modules;

use Rocketeer\Services\Modules\AbstractModule;

class DummyModule extends AbstractModule
{

    public function foo()
    {
        return 'foobar';
    }

    public function parent()
    {
        return $this->modulable->parent();
    }

    /**
     * @return string[]
     */
    public function getProvided()
    {
        return ['foo'];
    }
}
