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

namespace Rocketeer\Services\Environment\Modules;

use Rocketeer\Services\Environment\Pathfinder;
use Rocketeer\Services\Modules\AbstractModule;

/**
 * Abstract class for Pathfinder modules.
 */
abstract class AbstractPathfinderModule extends AbstractModule
{
    /**
     * @var Pathfinder
     */
    protected $modulable;
}
