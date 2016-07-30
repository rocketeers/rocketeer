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

namespace Rocketeer\Services\Bootstrapper;

use League\Container\ContainerAwareInterface;
use Rocketeer\Services\Bootstrapper\Modules\ConfigurationBootstrapper;
use Rocketeer\Services\Bootstrapper\Modules\PathsBootstrapper;
use Rocketeer\Services\Bootstrapper\Modules\TasksBootstrapper;
use Rocketeer\Services\Bootstrapper\Modules\UserBootstrapper;
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
 * @mixin UserBootstrapper
 */
class Bootstrapper implements ModulableInterface, ContainerAwareInterface
{
    use ModulableTrait;
    use ContainerAwareTrait;
}
