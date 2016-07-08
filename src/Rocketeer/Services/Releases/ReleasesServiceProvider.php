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

namespace Rocketeer\Services\Releases;

use League\Container\ServiceProvider\AbstractServiceProvider;

class ReleasesServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        ReleasesManager::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share(ReleasesManager::class)->withArgument($this->container);
    }
}
