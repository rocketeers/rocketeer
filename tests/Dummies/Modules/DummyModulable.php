<?php
namespace Rocketeer\Dummies\Modules;

use Rocketeer\Services\Modules\ModulableInterface;
use Rocketeer\Services\Modules\ModulableTrait;

class DummyModulable implements ModulableInterface
{
    use ModulableTrait;

    public function parent()
    {
        return 'parent';
    }
}
