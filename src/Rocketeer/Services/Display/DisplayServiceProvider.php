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

namespace Rocketeer\Services\Display;

use League\Container\ServiceProvider\AbstractServiceProvider;

class DisplayServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        QueueTimer::class,
        QueueExplainer::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share(QueueTimer::class)->withArgument($this->container);
        $this->container->share(QueueExplainer::class)->withArgument($this->container);
    }
}
