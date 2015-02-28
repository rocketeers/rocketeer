<?php
namespace Rocketeer\Services\Config;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Rocketeer\Services\Config\Dumpers\PhpReferenceDumper;
use Symfony\Component\Config\Definition\Dumper\XmlReferenceDumper;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Finder\Finder;

class Configuration extends Collection
{
    protected $rootNodes = [
        'application_name',
        'plugins',
        'logs',
        'default',
        'connections',
        'use_roles',
        'on',
    ];

    /**
     * Get an item from the collection by key.
     *
     * @param  string $key
     * @param  mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $key = in_array($key, $this->rootNodes) ? 'config.'.$key : $key;
        if ($value = Arr::get($this->items, $key, $default)) {
            return $value;
        }

        return value($default);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $key = in_array($key, $this->rootNodes) ? 'config.'.$key : $key;

        Arr::set($this->items, $key, $value);
    }

    /**
     * Set the available options and their values
     *
     * @param string $format
     * @param string $node
     *
     * @return string
     */
    public function getDefinition($format = 'yml', $node = null)
    {
        switch ($format) {
            case 'php':
                $dumper = new PhpReferenceDumper();
                break;

            case 'xml':
                $dumper = new XmlReferenceDumper();
                break;

            case 'yml':
            case 'yaml':
                $dumper = new YamlReferenceDumper();
                break;
        }

        $definition = new ConfigurationDefinition();
        $definition = $definition->getConfigTreeBuilder()->buildTree();
        if ($node) {
            $definition = $definition->getChildren()[$node];
        }

        return $dumper->dumpNode($definition);
    }

    /**
     * Publish the configuration somewhere
     *
     * @param string      $path
     * @param string|null $node
     */
    public function publish($path, $node = null)
    {
        if (is_dir($path)) {
            foreach (['config', 'hooks', 'paths', 'remote', 'scm', 'stages', 'strategies'] as $file) {
                $this->publish($path.'/'.$file.'.php', $file);
            }

            return;
        }

        $format        = pathinfo($path, PATHINFO_EXTENSION);
        $configuration = $this->getDefinition($format, $node);

        file_put_contents($path, $configuration);
    }
}
