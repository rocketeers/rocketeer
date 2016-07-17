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

namespace Rocketeer\Services\Ignition\Modules;

use Rocketeer\Services\Ignition\Bootstrapper;
use Rocketeer\Services\Modules\AbstractModule;

abstract class AbstractBootstrapperModule extends AbstractModule
{
    /**
     * @var Bootstrapper
     */
    protected $modulable;
}
