<?php
namespace Rocketeer\Services\Config\Definition;

use Symfony\Component\Config\Definition\TreeBuilder\NodeBuilder;

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
                ->prototype('variable')->end()
            ->end();
    }

}
