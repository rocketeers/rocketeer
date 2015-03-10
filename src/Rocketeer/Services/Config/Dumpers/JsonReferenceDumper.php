<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

        // Simply convert the PHP to JSON for simicity's sake
        $reference = str_replace('<?php ', null, $reference);
        $reference = eval($reference);
        $reference = json_encode($reference, JSON_PRETTY_PRINT);

        return $reference;
    }
}
