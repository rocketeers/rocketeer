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

namespace Rocketeer\Services\Roles;

use League\Container\ServiceProvider\AbstractServiceProvider;

class RolesServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        RolesManager::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share(RolesManager::class)->withArgument($this->container);
    }
}
