<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Strategies;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Rocketeer\Binaries\Scm\ScmInterface;
use Rocketeer\Services\Builders\Builder;
use Rocketeer\Services\Config\ContextualConfiguration;

class StrategiesServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        ScmInterface::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        /** @var ContextualConfiguration $config */
        $config = $this->container->get(ContextualConfiguration::class);

        // Bind SCM class
        $scm = $config->getContextually('scm.scm');
        $this->container->add(ScmInterface::class, function () use ($scm) {
            return $this->container->get(Builder::class)->buildBinary($scm);
        });
    }
}
