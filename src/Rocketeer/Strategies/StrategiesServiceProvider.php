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

namespace Rocketeer\Strategies;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Rocketeer\Binaries\Vcs\VcsInterface;
use Rocketeer\Services\Builders\Builder;
use Rocketeer\Services\Config\ContextualConfiguration;

class StrategiesServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        VcsInterface::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        /** @var ContextualConfiguration $config */
        $config = $this->container->get(ContextualConfiguration::class);

        // Bind VCS class
        $vcs = $config->getContextually('vcs.vcs');
        $this->container->add(VcsInterface::class, function () use ($vcs) {
            return $this->container->get(Builder::class)->buildBinary($vcs);
        });
    }
}
