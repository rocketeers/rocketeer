<?php
namespace Rocketeer\Services\Config;

use Illuminate\Filesystem\Filesystem;
use Rocketeer\Services\Config\Dumpers\JsonReferenceDumper;
use Rocketeer\Services\Config\Dumpers\PhpReferenceDumper;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\Dumper\XmlReferenceDumper;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;

class ConfigurationPublisher
{
    /**
     * @type ConfigurationDefinition
     */
    protected $definition;

    /**
     * @type Filesystem
     */
    protected $files;

    /**
     * ConfigurationPublisher constructor.
     *
     * @param ConfigurationDefinition $definition
     * @param Filesystem              $files
     */
    public function __construct(ConfigurationDefinition $definition, Filesystem $files)
    {
        $this->definition = $definition;
        $this->files      = $files;
    }

    /**
     * Set the available options and their values
     *
     * @param string      $format
     * @param string|null $node
     *
     * @return string
     */
    public function getDefinition($format = 'yml', $node = null)
    {
        switch ($format) {
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

    /**
     * Publish the configuration somewhere
     *
     * @param string      $path
     * @param string      $format
     * @param string|null $node
     */
    public function publish($path, $format = 'php', $node = null)
    {
        if (is_dir($path)) {
            foreach (['config', 'hooks', 'paths', 'remote', 'scm', 'stages', 'strategies'] as $file) {
                $this->publish($path.'/'.$file.'.'.$format, $format, $file);
            }

            return;
        }

        // If a single file was passed, infer format from extension
        $format        = $format ?: pathinfo($path, PATHINFO_EXTENSION);
        $configuration = $this->getDefinition($format, $node);

        $this->files->put($path, $configuration);
    }
}
