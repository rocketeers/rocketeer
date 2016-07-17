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

use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;
use Symfony\Component\Config\Definition\TreeBuilder\NodeBuilder;

class ConnectionsDefinition extends AbstractDefinition
{
    /**
     * @var string
     */
    protected $name = 'config';

    /**
     * @var string
     */
    protected $description = 'The main configuration of your application';

    /**
     * @param NodeBuilder $node
     *
     * @return NodeBuilder
     */
    protected function getChildren(NodeBuilder $node)
    {
        $node = $this->getOptionsNodes($node);
        $node = $this->getConnectionsNodes($node);
        $node = $this->getOverridesNodes($node);

        return $node;
    }

    /**
     * @param NodeBuilder $node
     *
     * @return mixed
     */
    protected function getOptionsNodes(NodeBuilder $node)
    {
        return $node
            ->scalarNode('application_name')
                ->info("The name of the application to deploy\nThis will create a folder of the same name in the root directory")
                ->defaultValue($this->value('config.application_name', '{application_name}'))
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
            ->arrayNode('plugins')
                ->info('The plugins to load')
                ->example(['Rocketeer\\Plugins\\Slack\\RocketeerSlack'])
                ->prototype('scalar')->end()
            ->end()
            ->closureNode('logs')
                ->info('The schema to use to name log files')
                ->defaultValue(function (\Rocketeer\Services\Connections\ConnectionsHandler $connections) {
                    return sprintf('%s-%s.log', $connections->getCurrentConnectionKey(), date('Ymd'));
                })
            ->end()
            ->arrayNode('default')
                ->info('The default remote connection(s) to execute tasks on')
                ->beforeNormalization()
                    ->ifString()
                    ->then(function ($default) {
                        return [$default];
                    })
                ->end()
                ->defaultValue([$this->getDefaultConnection()])
                ->prototype('scalar')->end()
            ->end();
    }

    /**
     * @param NodeBuilder $node
     *
     * @return \Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface
     */
    protected function getConnectionsNodes(NodeBuilder $node)
    {
        $node = $node->arrayNode('connections')
            ->info(<<< 'EOF'
You can leave all of this empty or remove it entirely if you don't want
to track files with credentials : Rocketeer will prompt you for your credentials
and store them locally.
There are four ways to define a credential:
'foobar'   - value is required, will never be prompted for it
''         - value is required, will be prompted for it once, then saved
true       - value is required, will be prompted for it every time
false|null - value is not required, will never be prompted for it
EOF
            )
            ->children();

        $connections = $this->value('config.connections');
        if (!$connections) {
            $connections = [
                'production' => [],
            ];
        }

        foreach ($connections as $name => $connection) {
            $connection = new Fluent($connection);

            $node = $node
                ->arrayNode($name)
                    ->beforeNormalization()
                        ->always(function ($servers) {
                            return $this->unifyServerDeclarations($servers);
                        })
                    ->end()
                    ->children()
                        ->arrayNode('servers')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('host')->defaultValue($connection->host)->end()
                                    ->scalarNode('username')->defaultValue($connection->username)->end()
                                    ->scalarNode('password')->defaultValue($connection->password)->end()
                                    ->scalarNode('key')->defaultValue($connection->key)->end()
                                    ->scalarNode('keyphrase')->defaultValue($connection->keyphrase)->end()
                                    ->booleanNode('agent')->defaultTrue()->end()
                                    ->scalarNode('root_directory')
                                        ->info("The root directory where your applications will be deployed.\nThis path needs to start at the root, ie. start with a /")
                                        ->defaultValue($connection['root'])
                                    ->end()
                                    ->arrayNode('roles')
                                        ->info('The roles this server has')
                                        ->defaultValue(['web'])
                                        ->prototype('scalar')->end()
                                    ->end()
                                    ->arrayNode('config')
                                        ->info('Options overrides for this server')
                                        ->useAttributeAsKey('name')
                                        ->prototype('array')
                                            ->prototype('variable')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();
        }

        return $node->end()->end();
    }

    /**
     * @param NodeBuilder $node
     *
     * @return mixed
     */
    protected function getOverridesNodes(NodeBuilder $node)
    {
        return $node
            ->arrayNode('on')
                ->info(<<< 'EOF'
In this section you can fine-tune the above configuration according
to the stage or connection currently in use.
Per example :
'stages' => [
    'staging' => [
        'scm' => ['branch' => 'staging'],
    ],
     'production' => [
       'scm' => ['branch' => 'master'],
     ],
],
EOF
                )
                ->children()
                    ->arrayNode('stages')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->prototype('variable')->end()
                        ->end()
                    ->end()
                    ->arrayNode('connections')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->prototype('variable')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    ////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////// HELPERS ////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * @return string
     */
    protected function getDefaultConnection()
    {
        $connections = (array) $this->value('config.connections');
        $defaultConnection = head(array_keys($connections)) ?: 'production';

        return $defaultConnection;
    }

    /**
     * @param array $servers
     *
     * @return array
     */
    protected function unifyServerDeclarations($servers)
    {
        // Unify flat declaration
        if (Arr::get($servers, 'host')) {
            return ['servers' => [$servers]];
        }

        // Unify single servers
        if (Arr::get($servers, 'servers.host')) {
            $servers['servers'] = [$servers['servers']];
        }

        // Unify all the things
        if (array_keys($servers) !== ['servers']) {
            $servers = Arr::get($servers, 'servers', $servers);
            $servers = ['servers' => array_values($servers)];
        }

        return $servers;
    }
}
