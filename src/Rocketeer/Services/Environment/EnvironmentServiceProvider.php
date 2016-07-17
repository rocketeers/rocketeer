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

namespace Rocketeer\Services\Environment;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Rocketeer\Services\Environment\Modules\ApplicationPathfinder;
use Rocketeer\Services\Environment\Modules\HomePathfinder;
use Rocketeer\Services\Environment\Modules\ServerPathfinder;

class EnvironmentServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        Pathfinder::class,
        Environment::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share(Environment::class)->withArgument($this->container);
        $this->container->share(Pathfinder::class, function () {
            $pathfinder = new Pathfinder($this->container);
            $pathfinder->register(new ApplicationPathfinder());
            $pathfinder->register(new ServerPathfinder());
            $pathfinder->register(new HomePathfinder());

            return $pathfinder;
        });
    }
}
