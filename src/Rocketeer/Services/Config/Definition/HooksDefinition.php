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
            ->arrayNode('before')
                ->useAttributeAsKey('name')
                ->prototype('variable')->end()
            ->end()
            ->arrayNode('after')
                ->useAttributeAsKey('name')
                ->prototype('variable')->end()
            ->end()
            ->arrayNode('custom')
                ->useAttributeAsKey('name')
                ->prototype('scalar')->end()
            ->end()
            ->arrayNode('roles')
                ->prototype('scalar')->end()
            ->end();
    }
}
