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

namespace Rocketeer\Services\Builders;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Rocketeer\Services\Builders\Modules\BinariesBuilder;
use Rocketeer\Services\Builders\Modules\CommandsBuilder;
use Rocketeer\Services\Builders\Modules\StrategiesBuilder;
use Rocketeer\Services\Builders\Modules\TasksBuilder;

class BuilderServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        Builder::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share(Builder::class, function () {
            $builder = new Builder($this->container);
            $builder->register(new BinariesBuilder());
            $builder->register(new CommandsBuilder());
            $builder->register(new StrategiesBuilder());
            $builder->register(new TasksBuilder());

            return $builder;
        });
    }
}
