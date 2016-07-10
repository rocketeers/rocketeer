<?php
namespace Rocketeer\Services\Modules;

use RuntimeException;

class ModuleNotFoundException extends RuntimeException
{
    /**
     * @param string $module
     * @param string $modulable
     */
    public function __construct($module, $modulable)
    {
        parent::__construct('Method '.$module.' not found in modules of '.$modulable);
    }
}
