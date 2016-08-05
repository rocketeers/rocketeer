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

namespace Rocketeer\Services\Events;

use League\Container\ServiceProvider\AbstractServiceProvider;

class EventsServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        TaggableEmitter::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share(TaggableEmitter::class);
    }
}
