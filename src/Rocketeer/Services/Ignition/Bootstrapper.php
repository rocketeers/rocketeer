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

use Dotenv\Dotenv;
use League\Container\ContainerAwareInterface;
use Rocketeer\Facades\Rocketeer;
use Rocketeer\Services\Ignition\Modules\ConfigurationModule;
use Rocketeer\Services\Ignition\Modules\PathsModule;
use Rocketeer\Services\Ignition\Modules\TasksModule;
use Rocketeer\Services\Modules\ModulableInterface;
use Rocketeer\Services\Modules\ModulableTrait;
use Rocketeer\Traits\ContainerAwareTrait;
use Symfony\Component\ClassLoader\Psr4ClassLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Ignites Rocketeer's custom configuration, tasks, events and paths
 * depending on what Rocketeer is used on.
 *
 * @mixin ConfigurationModule
 * @mixin PathsModule
 * @mixin TasksModule
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Bootstrapper implements ModulableInterface, ContainerAwareInterface
{
    use ModulableTrait;
    use ContainerAwareTrait;
}
