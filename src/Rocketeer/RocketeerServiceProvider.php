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

namespace Rocketeer;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Rocketeer\Traits\HasLocatorTrait;

/**
 * Bind the various Rocketeer classes to a Container.
 */
class RocketeerServiceProvider extends AbstractServiceProvider
{
    use HasLocatorTrait;

    /**
     * @var array
     */
    protected $provides = [
        Rocketeer::class,
    ];

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->container->share(Rocketeer::class)->withArgument($this->container);
    }
}
