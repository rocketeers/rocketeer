<?php
namespace Rocketeer\Services\Config\Dumpers;

use Rocketeer\Services\Config\TreeBuilder\ClosureNode;
use SuperClosure\Analyzer\AstAnalyzer;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\EnumNode;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\PrototypedArrayNode;

/**
 * Dumps a Symfony reference in PHP format
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class PhpReferenceDumper extends AbstractReferenceDumper
{
    /**
     * @param NodeInterface $node
     * @param string|null   $namespace
     *
     * @return string
     */
    public function dumpNode(NodeInterface $node, $namespace = null)
    {
        return '<?php return array('.PHP_EOL.parent::dumpNode($node, $namespace).PHP_EOL.');';
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    protected function serializeValue($value)
    {
        return var_export($value, true);
    }
}
