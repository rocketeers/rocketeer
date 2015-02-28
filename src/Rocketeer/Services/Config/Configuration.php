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
    /**
     * @type LoaderInterface
     */
    private $loader;

    /**
     * @type ConfigurationDefinition
     */
    private $definition;

    /**
     * Configuration constructor.
     *
     * @param LoaderInterface         $loader
     * @param ConfigurationDefinition $definition
     */
    public function __construct(LoaderInterface $loader, ConfigurationDefinition $definition)
    {
        $this->folder     = __DIR__.'/../../../config';
        $this->loader     = $loader;
        $this->definition = $definition;

        $this->loadConfiguration();
    }

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
        if ($value = Arr::get($this->items, $key, $default)) {
            return $value;
        }

        return value($default);
    }

    /**
     * Set the available options and their values
     *
     * @param string $format
     *
     * @return string
     */
    public function getDefinition($format = 'yml')
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

        return $dumper->dump($this->definition);
    }

    /**
     * Publish the configuration somewhere
     *
     * @param string $path
     */
    public function publish($path)
    {
        $format        = pathinfo($path, PATHINFO_EXTENSION);
        $configuration = $this->getDefinition($format);

        file_put_contents($path, $configuration);
    }

    /**
     * Load the configuration in memory
     *
     * @return array
     */
    protected function loadConfiguration()
    {
        $finder = (new Finder())->in($this->folder);
        foreach ($finder as $file) {
            $key                 = $file->getBasename('.php');
            $configuration[$key] = $this->loader->load($file->getBasename());
        }

        $processor   = new Processor();
        $this->items = $processor->processConfiguration(
            $this->definition,
            [$configuration]
        );
    }
}
