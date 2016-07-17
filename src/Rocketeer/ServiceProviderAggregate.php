<?php
namespace Rocketeer;

use Rocketeer\Plugins\AbstractPlugin;

class ServiceProviderAggregate extends \League\Container\ServiceProvider\ServiceProviderAggregate
{
    /**
     * @var string[]
     */
    protected $plugins = [];

    /**
     * {@inheritdoc}
     */
    public function add($provider)
    {
        // Unify to instances
        if (is_string($provider) && class_exists($provider)) {
            $provider = new $provider;
        }

        // Register plugin
        if ($provider instanceof AbstractPlugin) {
            $class = get_class($provider);
            if (!array_key_exists($class, $this->plugins)) {
                $this->plugins[$class] = $provider;
            } else {
                return;
            }
        }

        return parent::add($provider);
    }

    /**
     * @return AbstractPlugin[]
     */
    public function getPlugins()
    {
        return $this->plugins;
    }
}
