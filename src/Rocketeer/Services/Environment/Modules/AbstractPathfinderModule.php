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

use League\Container\ContainerAwareInterface;
use Rocketeer\Services\Environment\Pathfinder;
use Rocketeer\Services\Modules\AbstractModule;
use Rocketeer\Traits\ContainerAwareTrait;

abstract class AbstractPathfinderModule extends AbstractModule implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var Pathfinder
     */
    protected $modulable;
}
