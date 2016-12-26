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
 * Configuration schema for the remote node.
 */
class RemoteDefinition extends AbstractDefinition
{
    /**
     * @var string
     */
    protected $name = 'remote';

    /**
     * @var string
     */
    protected $description = 'Options related to the remote server';

    /**
     * @param NodeBuilder $node
     *
     * @return NodeBuilder
     */
    protected function getChildren(NodeBuilder $node)
    {
        return $node
            ->arrayNode('variables')
                ->info('Variables about the servers')
                ->children()
                    ->scalarNode('directory_separator')->defaultValue('/')->end()
                    ->scalarNode('line_endings')->defaultValue("\n")->end()
                ->end()
            ->end()
            ->arrayNode('directories')
                ->info("Paths/names of folders to use on server.\nFull path of current release will be at {root_directory}/{app_directory}/{current}/{subdirectory}")
                ->children()
                    ->scalarNode('app_directory')
                        ->info("The folder the application will be deployed in.\nLeave empty to use `application_name` as your folder name")
                    ->end()
                    ->scalarNode('subdirectory')
                        ->info("If the core of your application (ie. where dependencies/migrations/etc.) need to be run is in a subdirectory, specify it there (per example 'my_subdirectory')")
                    ->end()
                    ->scalarNode('current')
                        ->info('The name of the folder containing the current release')
                        ->defaultValue('current')
                    ->end()
                    ->scalarNode('releases')
                        ->info('The name of the folder containing all past and current releases')
                        ->defaultValue('releases')
                    ->end()
                    ->scalarNode('shared')
                        ->info('The name of the folder containing files shared between releases')
                        ->defaultValue('shared')
                    ->end()
                ->end()
            ->end()
            ->enumNode('symlink')
                ->info('The way symlinks are created')
                ->values(['absolute', 'relative'])
                ->defaultValue('absolute')
            ->end()
            ->integerNode('keep_releases')
                ->info('The number of releases to keep on server at all times')
                ->min(0)
                ->defaultValue(4)
            ->end()
            ->arrayNode('shared')
                ->info('A list of folders/file to be shared between releases')
                ->example(['logs', 'public/uploads'])
                ->prototype('scalar')->end()
            ->end()
            ->arrayNode('permissions')
                ->info('Files permissions related settings')
                ->children()
                    ->arrayNode('files')
                        ->info('The folders and files to set as web writable')
                        ->example(['storage', 'public'])
                        ->prototype('scalar')->end()
                    ->end()
                    ->closureNode('callback')
                        ->info('What actions will be executed to set permissions on the folder above')
                        ->defaultValue(function ($file) {
                            return [
                                'chmod -R 755 '.$file,
                                'chmod -R g+s '.$file,
                                'chown -R www-data:www-data '.$file,
                            ];
                        })
                    ->end()
                ->end()
            ->end()
            ->scalarNode('sudo')
                ->info("Enable use of sudo for some commands\nYou can specify a sudo user by doing\n'sudo' => 'the_user'")
                ->defaultTrue()
            ->end()
            ->arrayNode('sudoed')
                ->info('An array of commands to run under sudo')
                ->defaultValue([])
                ->prototype('scalar')->end()
            ->end()
            ->booleanNode('shell')
                ->info('If enabled will force a shell to be created which is required for some tools like RVM or NVM')
                ->defaultTrue()
            ->end()
            ->arrayNode('shelled')
                ->info('An array of commands to run under shell')
                ->defaultValue(['which', 'ruby', 'npm', 'bower', 'bundle', 'grunt'])
                ->prototype('scalar')->end()
            ->end();
    }
}
