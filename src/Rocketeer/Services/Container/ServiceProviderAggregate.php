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

namespace Rocketeer\Services\Container;

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
            $provider = new $provider();
        }

        // Register plugin
        if ($provider instanceof AbstractPlugin) {
            $class = get_class($provider);
            if (!array_key_exists($class, $this->plugins)) {
                $this->plugins[$class] = $provider;
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
