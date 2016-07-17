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

namespace Rocketeer\Services\Ignition;

use League\Container\ContainerAwareInterface;
use Rocketeer\Facades\Rocketeer;
use Rocketeer\Services\Ignition\Modules\ConfigurationBootstrapper;
use Rocketeer\Services\Ignition\Modules\PathsBootstrapper;
use Rocketeer\Services\Ignition\Modules\TasksBootstrapper;
use Rocketeer\Services\Modules\ModulableInterface;
use Rocketeer\Services\Modules\ModulableTrait;
use Rocketeer\Traits\ContainerAwareTrait;

/**
 * Ignites Rocketeer's custom configuration, tasks, events and paths
 * depending on what Rocketeer is used on.
 *
 * @mixin ConfigurationBootstrapper
 * @mixin PathsBootstrapper
 * @mixin TasksBootstrapper
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Bootstrapper implements ModulableInterface, ContainerAwareInterface
{
    use ModulableTrait;
    use ContainerAwareTrait;
}
