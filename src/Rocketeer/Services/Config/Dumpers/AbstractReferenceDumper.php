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

use Rocketeer\Services\Config\TreeBuilder\ClosureNode;
use SuperClosure\Analyzer\TokenAnalyzer;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\EnumNode;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\PrototypedArrayNode;

abstract class AbstractReferenceDumper
{
    /**
     * @type array
     */
    protected $comments = [];

    /**
     * @type string
     */
    protected $default;

    /**
     * @type array
     */
    protected $defaultArray;

    /**
     * @type string|string[]
     */
    protected $example;

    /**
     * @type boolean
     */
    protected $isCoreNode;

    /**
     * @type string
     */
    protected $reference;

    /**
     * @param ConfigurationInterface $configuration
     * @param                        string null            $namespace
     *
     * @return string
     */
    public function dump(ConfigurationInterface $configuration, $namespace = null)
    {
        return $this->dumpNode($configuration->getConfigTreeBuilder()->buildTree(), $namespace);
    }

    /**
     * @param NodeInterface $node
     * @param string|null   $namespace
     *
     * @return string
     */
    public function dumpNode(NodeInterface $node, $namespace = null)
    {
        $this->reference = '';
        $this->writeNode($node, 1, true, $namespace);

        $reference       = $this->reference;
        $this->reference = null;

        return $reference;
    }

    /**
     * @param NodeInterface $node
     * @param int           $depth
     */
    private function writeNode(NodeInterface $node, $depth = 0)
    {
        // Reinitialize fields
        $this->comments     = [];
        $this->default      = '';
        $this->defaultArray = null;
        $children           = null;
        $this->example      = $node->getExample();
        $this->isCoreNode   = $node->getParent() && $node->getParent()->getName() === 'rocketeer';

        if ($node instanceof ArrayNode) {
            $children = $node->getChildren();

            if ($node instanceof PrototypedArrayNode) {
                $prototype = $node->getPrototype();

                if ($prototype instanceof ArrayNode) {
                    $children = $prototype->getChildren();
                }

                // check for attribute as key
                if ($key = $node->getKeyAttribute()) {
                    $keyNodeClass = 'Symfony\Component\Config\Definition\\'.($prototype instanceof ArrayNode ? 'ArrayNode' : 'ScalarNode');
                    $keyNode      = new $keyNodeClass($key, $node);
                    $keyNode->setInfo('Prototype');

                    // add children
                    foreach ($children as $childNode) {
                        $keyNode->addChild($childNode);
                    }
                    $children = [$key => $keyNode];
                }

                if (!$children) {
                    if ($node->hasDefaultValue() && count($this->defaultArray = $node->getDefaultValue())) {
                        $this->default = '';
                    } elseif (!is_array($this->example)) {
                        $this->default = [];
                    }
                }
            }
        } elseif ($node instanceof EnumNode) {
            $this->comments[] = 'One of '.implode(', ', array_map('json_encode', $node->getValues()));
            $this->default    = $node->getDefaultValue();
        } elseif ($node instanceof ClosureNode) {
            $this->default = $node->getDefaultValue();
            $analyzer      = new TokenAnalyzer();
            $this->default = $analyzer->analyze($this->default)['code'];
        } else {
            $this->default = null;

            if ($node->hasDefaultValue()) {
                $this->default = $node->getDefaultValue();

                if (is_array($this->default)) {
                    if (count($this->defaultArray = $node->getDefaultValue())) {
                        $this->default = '';
                    } elseif (!is_array($this->example)) {
                        $this->default = [];
                    }
                }
            }
        }

        // required?
        if ($node->isRequired()) {
            $this->comments[] = 'Required';
        }

        // example
        if ($this->example && !is_array($this->example)) {
            $this->comments[] = 'Example: '.$this->example;
        }

        // Format comments and values
        $this->comments = count($this->comments) ? '// '.implode(', ', $this->comments) : '';
        $name           = $this->serializeValue($node->getName()).' => ';
        $format         = '%-20s %s %s';

        if ($node instanceof ArrayNode) {
            $name .= '[';
            $this->default = (!$this->example && !$children && !$this->defaultArray) ? '],' : null;
            $format        = '%s%s %s';
        } elseif ($node instanceof ClosureNode) {
            $this->default .= ',';
        } else {
            $this->default = $this->default === "\n" ? '"\n"' : $this->serializeValue($this->default);
            $this->default .= ',';
        }

        // Output informations
        $this->outputInformations($node, $depth);

        $this->default = str_replace("\n", sprintf("\n%".($depth * 4)."s ", ' '), $this->default);
        $text          = rtrim(sprintf($format, $name, $this->default, $this->comments), ' ');

        // Output main value
        $this->writeLine($text, $depth * 4);

        $this->outputDefaults($depth);
        $this->outputExamples($depth);

        if ($node instanceof ArrayNode) {
            $this->outputChildren($children, $depth);
        }
    }

    /**
     * Output an array.
     *
     * @param array $array
     * @param int   $depth
     * @param bool  $comments
     */
    private function writeArray(array $array, $depth, $comments = false)
    {
        // Else dump each value on its own line
        $isIndexed = array_values($array) === $array;
        $method    = $comments ? 'writeComment' : 'writeLine';

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $val = '';
            } else {
                $val = $value;
            }

            $key = $this->serializeValue($key);
            $val = $this->serializeValue($val);
            if ($isIndexed) {
                $this->$method($val.',', $depth);
            } else {
                $this->$method(sprintf('%s => %s,', $key, $val), $depth);
            }

            if (is_array($value)) {
                $this->writeArray($value, $depth + 1);
            }
        }
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// OUTPUT ////////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Output the node's examples
     *
     * @param $depth
     */
    private function outputExamples($depth)
    {
        if (is_array($this->example)) {
            $message = count($this->example) > 1 ? 'Examples' : 'Example';

            $this->writeComment($message.':', $depth * 4 + 4);
            $this->writeArray($this->example, $depth * 4 + 4, true);
        }
    }

    /**
     * Output the node's default value
     *
     * @param integer $depth
     */
    private function outputDefaults($depth)
    {
        if ($this->defaultArray) {
            $message    = count($this->defaultArray) > 1 ? 'Defaults' : 'Default';
            $childDepth = $depth * 4 + 4;

            $this->writeComment($message.':', $childDepth);
            $this->writeArray($this->defaultArray, $childDepth);
        }
    }

    /**
     * Output the children of the node
     *
     * @param  array  $children
     * @param integer $depth
     */
    private function outputChildren($children, $depth)
    {
        if ($children) {
            foreach ($children as $childNode) {
                $this->writeNode($childNode, $depth + 1);
            }
        }

        if ($children || $this->example || $this->defaultArray) {
            $this->writeLine('],', $depth * 4);
        }
    }

    /**
     * @param NodeInterface $node
     * @param integer       $depth
     */
    private function outputInformations(NodeInterface $node, $depth)
    {
        if ($info = $node->getInfo()) {
            $this->writeLine('');

            $info = str_replace("\n", sprintf("\n%".($depth * 4)."s// ", ' '), $info);
            $this->writeComment($info, $depth * 4);

            if ($this->isCoreNode) {
                $this->writeLine(str_repeat('/', 70), $depth * 4);
                $this->writeLine('');
            }
        }
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param mixed $value
     *
     * @return string
     */
    protected function serializeValue($value)
    {
        return $value;
    }

    /**
     * Outputs a single config reference line.
     *
     * @param string $text
     * @param int    $indent
     */
    protected function writeLine($text, $indent = 0)
    {
        $indent = strlen($text) + $indent;
        $format = '%'.$indent.'s';

        $this->reference .= sprintf($format, $text)."\n";
    }

    /**
     * @param string $comment
     * @param int    $depth
     */
    protected function writeComment($comment, $depth)
    {
        $this->writeLine('// '.$comment, $depth);
    }
}
