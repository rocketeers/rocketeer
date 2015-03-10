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

use Symfony\Component\Config\Definition\Builder\VariableNodeDefinition;

class ClosureNodeDefinition extends VariableNodeDefinition
{
    protected function instantiateNode()
    {
        return new ClosureNode($this->name, $this->parent);
    }
}
