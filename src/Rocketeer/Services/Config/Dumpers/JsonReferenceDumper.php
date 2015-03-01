<?php
namespace Rocketeer\Services\Config\Dumpers;

use Symfony\Component\Config\Definition\NodeInterface;

class JsonReferenceDumper extends PhpReferenceDumper
{
    /**
     * @param NodeInterface $node
     * @param string|null   $namespace
     *
     * @return string
     */
    public function dumpNode(NodeInterface $node, $namespace = null)
    {
        $reference = parent::dumpNode($node, $namespace);
        $reference = str_replace('<?php ', null, $reference);
        $reference = eval($reference);
        $reference = json_encode($reference, JSON_PRETTY_PRINT);

        return $reference;
    }
}
