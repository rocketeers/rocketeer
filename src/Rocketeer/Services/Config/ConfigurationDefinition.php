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

namespace Rocketeer\Services\Config;

use Rocketeer\Services\Config\Definition\AbstractDefinition;
use Rocketeer\Services\Config\Definition\ConnectionsDefinition;
use Rocketeer\Services\Config\Definition\HooksDefinition;
use Rocketeer\Services\Config\Definition\PathsDefinition;
use Rocketeer\Services\Config\Definition\PluginsDefinition;
use Rocketeer\Services\Config\Definition\RemoteDefinition;
use Rocketeer\Services\Config\Definition\StagesDefinition;
use Rocketeer\Services\Config\Definition\StrategiesDefinition;
use Rocketeer\Services\Config\Definition\VcsDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Schema validating Rocketeer's configuration format.
 */
class ConfigurationDefinition extends AbstractDefinition
{
    /**
     * @var array
     */
    protected $definitions = [
        ConnectionsDefinition::class,
        HooksDefinition::class,
        PathsDefinition::class,
        RemoteDefinition::class,
        VcsDefinition::class,
        StagesDefinition::class,
        StrategiesDefinition::class,
        PluginsDefinition::class,
    ];

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $node = $builder->root('rocketeer')->children();
        foreach ($this->definitions as $definition) {
            /** @var AbstractDefinition $definition */
            $definition = new $definition();
            $definition->setValues($this->values);
            $definition = $definition->getConfigTreeBuilder();

            $node = $node->append($definition);
        }
        $node = $node->end();

        return $builder;
    }
}
