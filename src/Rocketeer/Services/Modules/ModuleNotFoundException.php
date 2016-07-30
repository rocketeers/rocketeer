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

use RuntimeException;

/**
 * Exception for when calling a method on a modulable
 * and no matching module is found.
 */
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
