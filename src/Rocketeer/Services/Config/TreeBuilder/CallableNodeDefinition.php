<?php
namespace Rocketeer\Services\Config\TreeBuilder;

use Symfony\Component\Config\Definition\Builder\VariableNodeDefinition;

class CallableNodeDefinition extends VariableNodeDefinition
{
    protected function instantiateNode()
    {
        return new CallableNode($this->name, $this->parent);
    }
}
