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

        $this->nodeMapping['callable'] = __NAMESPACE__.'\\CallableNodeDefinition';
    }

    /**
     * Creates a child callable node.
     *
     * @param string $name the name of the node
     *
     * @return CallableNodeDefinition The child node
     */
    public function callableNode($name)
    {
        return $this->node($name, 'callable');
    }
}
