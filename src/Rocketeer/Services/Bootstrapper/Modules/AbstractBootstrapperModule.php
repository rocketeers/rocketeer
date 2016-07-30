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

namespace Rocketeer\Services\Bootstrapper\Modules;

use Rocketeer\Services\Bootstrapper\Bootstrapper;
use Rocketeer\Services\Modules\AbstractModule;

/**
 * Abstract for modules of the Bootstrapper class.
 */
abstract class AbstractBootstrapperModule extends AbstractModule
{
    /**
     * @var Bootstrapper
     */
    protected $modulable;
}
