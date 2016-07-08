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

namespace Rocketeer\Services\History;

use League\Container\ServiceProvider\AbstractServiceProvider;

class HistoryServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        History::class,
        LogsHandler::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share(History::class);
        $this->container->share(LogsHandler::class)->withArgument($this->container);
    }
}
