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

namespace Rocketeer\Services\Builders\Modules;

use Rocketeer\Services\Builders\Builder;
use Rocketeer\Services\Modules\AbstractModule;

/**
 * Abstract for modules of the Builder class.
 */
abstract class AbstractBuilderModule extends AbstractModule
{
    /**
     * @var Builder
     */
    protected $modulable;
}
