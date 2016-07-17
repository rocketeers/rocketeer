<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Services\Config\Files;

use Rocketeer\Container;
use Rocketeer\Services\Config\ConfigurationDefinition;
use Rocketeer\Traits\ContainerAwareTrait;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\Dumper\XmlReferenceDumper;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\Config\Definition\Dumpers\JsonReferenceDumper;
use Symfony\Component\Config\Definition\Dumpers\PhpReferenceDumper;

class ConfigurationPublisher
{
    use ContainerAwareTrait;

    /**
     * @var array
     */
    public static $formats = [
        'php',
        'json',
        'yaml',
        'xml',
    ];

    /**
     * @var ConfigurationDefinition
     */
    protected $definition;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->definition = new ConfigurationDefinition();
    }

    /**
     * @param ConfigurationDefinition $definition
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;
    }

    /**
     * @param string $format
     * @param bool   $consolidated
     *
     * @return string
     */
    public function publish($format = 'php', $consolidated = false)
    {
        $destination = $this->paths->getConfigurationPath();

        // Create directory
        if (!$this->files->isDirectory($destination)) {
            $this->files->createDir($destination);
        }

        // Consolidate or not configuration
        if ($consolidated) {
            $destination .= '/config.'.$format;
        }

        // Unzip configuration files
        $this->publishNode($destination, $format);

        return $destination;
    }

    /**
     * Publish the configuration somewhere.
     *
     * @param string      $path
     * @param string      $format
     * @param string|null $node
     */
    public function publishNode($path, $format = 'php', $node = null)
    {
        if ($this->files->isDirectory($path)) {
            foreach (['config', 'hooks', 'paths', 'remote', 'scm', 'stages', 'strategies'] as $file) {
                $this->publishNode($path.'/'.$file.'.'.$format, $format, $file);
            }

            return;
        }

        // If a single file was passed, infer format from extension
        $format = $format ?: pathinfo($path, PATHINFO_EXTENSION);
        $configuration = $this->getDefinition($format, $node);

        $this->files->put($path, $configuration);
    }

    /**
     * Set the available options and their values.
     *
     * @param string      $format
     * @param string|null $node
     *
     * @return string
     */
    public function getDefinition($format = 'yml', $node = null)
    {
        switch (strtolower($format)) {
            case 'json':
                $dumper = new JsonReferenceDumper();
                break;

            case 'xml':
                $dumper = new XmlReferenceDumper();
                break;

            case 'yml':
            case 'yaml':
                $dumper = new YamlReferenceDumper();
                break;

            case 'php':
            default:
                $dumper = new PhpReferenceDumper();
                break;
        }

        $definition = $this->definition;
        $definition = $definition->getConfigTreeBuilder()->buildTree();
        if ($node && $definition instanceof ArrayNode) {
            $definition = $definition->getChildren()[$node];
        }

        return $dumper->dumpNode($definition);
    }
}
