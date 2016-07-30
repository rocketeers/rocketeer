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
 * Configuration schema for the stages node.
 */
class StagesDefinition extends AbstractDefinition
{
    /**
     * @var string
     */
    protected $name = 'stages';

    /**
     * @var string
     */
    protected $description = "The multiples stages of your application.\nIf you don't know what this does, then you don't need it";

    /**
     * @param NodeBuilder $node
     *
     * @return NodeBuilder
     */
    protected function getChildren(NodeBuilder $node)
    {
        return $node
            ->arrayNode('stages')
                ->info("Adding entries to this array will split the remote folder in stages\nExample: /var/www/yourapp/staging and /var/www/yourapp/production")
                ->example(['staging', 'production'])
                ->prototype('scalar')->end()
            ->end()
            ->arrayNode('default')
                ->info("The default stage to execute tasks on when --stage is not provided.\nFalsey means all of them")
                ->prototype('scalar')->end()
                ->beforeNormalization()
                    ->ifString()
                    ->then(function ($default) {
                        return $default ? [$default] : null;
                    })
                ->end()
            ->end();
    }
}
