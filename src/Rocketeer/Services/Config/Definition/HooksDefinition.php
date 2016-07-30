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
 * Configuration schema for the hooks node.
 */
class HooksDefinition extends AbstractDefinition
{
    /**
     * @var string
     */
    protected $name = 'hooks';

    /**
     * @var string
     */
    protected $description = 'Here you can customize Rocketeer by adding tasks, strategies, etc.';

    /**
     * @param NodeBuilder $node
     *
     * @return NodeBuilder
     */
    protected function getChildren(NodeBuilder $node)
    {
        return $node
            ->arrayNode('events')
                ->children()
                    ->arrayNode('before')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->prototype('variable')->end()
                        ->end()
                    ->end()
                    ->arrayNode('after')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->prototype('variable')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('tasks')
                ->info('Here you can quickly add custom tasks to Rocketeer, as well as to its CLI')
                ->useAttributeAsKey('name')
                ->prototype('variable')->end()
            ->end()
            ->arrayNode('roles')
                ->info('Define roles to assign to tasks'.PHP_EOL."eg. 'db' => ['Migrate']")
                ->useAttributeAsKey('name')
                ->prototype('variable')->end()
            ->end();
    }
}
