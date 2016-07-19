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

namespace Rocketeer\Services\Ignition\Modules;

class ConfigurationBootstrapper extends AbstractBootstrapperModule
{
    /**
     * Load the custom files (tasks, events, ...).
     */
    public function bootstrapConfiguration()
    {
        // Reload configuration
        $this->config->replace(
            $this->configurationLoader->getConfiguration()
        );

        $this->bootstrapPlugins();
    }

    /**
     * Load any configured plugins.
     */
    protected function bootstrapPlugins()
    {
        $plugins = (array) $this->config->get('plugins.loaded');
        $plugins = array_filter($plugins, 'class_exists');
        foreach ($plugins as $plugin) {
            $this->container->addServiceProvider($plugin);
        }
    }

    /**
     * @return string[]
     */
    public function getProvided()
    {
        return [
            'bootstrapConfiguration',
            'bootstrapPluginsConfiguration',
        ];
    }
}
