<?php
namespace Rocketeer\Services\Config\TreeBuilder;

use Symfony\Component\Config\Definition\Builder\VariableNodeDefinition;

class ClosureNodeDefinition extends VariableNodeDefinition
{
    protected function instantiateNode()
    {
        return new ClosureNode($this->name, $this->parent);
    }
}
