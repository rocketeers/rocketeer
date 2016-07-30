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

namespace Rocketeer\Services\Config\Definition;

use Symfony\Component\Config\Definition\TreeBuilder\NodeBuilder;

/**
 * Configuration schema for the plugins node.
 */
class PluginsDefinition extends AbstractDefinition
{
    /**
     * @var string
     */
    protected $name = 'plugins';

    /**
     * @var string
     */
    protected $description = 'Configuration for Rocketeer plugins';

    /**
     * {@inheritdoc}
     */
    protected function getChildren(NodeBuilder $node)
    {
        return $node
            ->arrayNode('loaded')
                ->info('The plugins to load')
                ->example(['Rocketeer\\Plugins\\Slack\\RocketeerSlack'])
                ->prototype('scalar')->end()
            ->end()
            ->arrayNode('config')
                ->useAttributeAsKey('name')
                ->normalizeKeys(false)
                ->prototype('variable')->end()
            ->end();
    }
}
