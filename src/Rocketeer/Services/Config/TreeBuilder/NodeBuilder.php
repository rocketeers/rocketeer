<?php
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
