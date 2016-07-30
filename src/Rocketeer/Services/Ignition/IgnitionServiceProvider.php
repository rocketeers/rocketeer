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

use League\Container\ServiceProvider\AbstractServiceProvider;

class IgnitionServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        PluginsIgniter::class,
        RocketeerIgniter::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share(PluginsIgniter::class)->withArgument($this->container);
        $this->container->share(RocketeerIgniter::class)->withArgument($this->container);
    }
}
