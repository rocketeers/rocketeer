<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Config\TreeBuilder;

class NodeBuilder extends \Symfony\Component\Config\Definition\Builder\NodeBuilder
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->nodeMapping['closure'] = __NAMESPACE__.'\\ClosureNodeDefinition';
    }

    /**
     * Creates a child callable node.
     *
     * @param string $name the name of the node
     *
     * @return ClosureNodeDefinition The child node
     */
    public function closureNode($name)
    {
        return $this->node($name, 'closure');
    }
}
