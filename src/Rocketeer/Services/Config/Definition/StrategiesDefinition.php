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
 * Configuration schema for the strategies node.
 */
class StrategiesDefinition extends AbstractDefinition
{
    /**
     * @var string
     */
    protected $name = 'strategies';

    /**
     * @var string
     */
    protected $description = 'Here you can configure in a modular way which tasks to use to execute various core parts of your deployment\'s flow';

    /**
     * @param NodeBuilder $node
     *
     * @return NodeBuilder
     */
    protected function getChildren(NodeBuilder $node)
    {
        return $node
            ->scalarNode('check')
                ->info('Which strategy to use to check the server')
                ->defaultValue('Polyglot')
            ->end()
            ->scalarNode('create-release')
                ->info('Which strategy to use to create a new release and prepare its dependencies')
                ->defaultValue('Clone')
            ->end()
            ->scalarNode('deploy')
                ->info('Which strategy to use to send a release online')
                ->defaultValue('Rolling')
            ->end()
            ->scalarNode('rollback')
                ->info('Which strategy to use to rollback to a previous release')
                ->defaultValue('Rolling')
            ->end()
            ->scalarNode('test')
                ->info('Which strategy to use to test your application')
                ->defaultValue('Phpunit')
            ->end()
            ->scalarNode('migrate')
                ->info('Which strategy to use to migrate your database')
            ->end()
            ->scalarNode('dependencies')
                ->info("Which strategy to use to install your application's dependencies")
                ->defaultValue('Polyglot')
            ->end();
    }
}
