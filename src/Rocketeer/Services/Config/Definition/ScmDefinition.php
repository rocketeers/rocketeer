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
 * Configuration schema for the SCM node.
 */
class ScmDefinition extends AbstractDefinition
{
    /**
     * @var string
     */
    protected $name = 'scm';

    /**
     * @var string
     */
    protected $description = 'The configuration of your repository';

    /**
     * @param NodeBuilder $node
     *
     * @return NodeBuilder
     */
    protected function getChildren(NodeBuilder $node)
    {
        $node = $this->getCredentialsNodes($node);
        $node = $this->getOptionsNodes($node);

        return $node;
    }

    /**
     * @param NodeBuilder $node
     *
     * @return NodeBuilder
     */
    protected function getCredentialsNodes(NodeBuilder $node)
    {
        return $node
            ->enumNode('scm')
                ->info('The SCM used')
                ->values(['git', 'svn', 'hg'])
                ->isRequired()
                ->defaultValue('git')
            ->end()
            ->scalarNode('repository')
                ->info('The SSH/HTTPS address to your repository')
                ->example('https://github.com/vendor/website.git')
                ->defaultValue($this->value('scm.repository'))
            ->end()
            ->scalarNode('username')
                ->defaultValue($this->value('scm.username'))
            ->end()
            ->scalarNode('password')
                ->defaultValue($this->value('scm.password'))
            ->end();
    }

    /**
     * @param NodeBuilder $node
     *
     * @return NodeBuilder
     */
    protected function getOptionsNodes(NodeBuilder $node)
    {
        return $node
            ->scalarNode('branch')
                ->info('The branch to deploy')
                ->defaultValue('master')
            ->end()
            ->scalarNode('shallow')
                ->info("Whether your SCM should do a \"shallow\" clone of the repository or not - this means a clone with just the latest state of your application (no history).\nIf you're having problems cloning, try setting this to false")
                ->defaultTrue()
            ->end()
            ->scalarNode('submodules')
                ->info("Recursively pull in submodules.\nWorks only with Git")
                ->defaultTrue()
            ->end();
    }
}
