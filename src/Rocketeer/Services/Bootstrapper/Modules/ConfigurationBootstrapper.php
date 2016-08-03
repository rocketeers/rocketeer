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

namespace Rocketeer\Services\Bootstrapper\Modules;

use Dotenv\Dotenv;

/**
 * Loads the user's configuration and register
 * any defined plugins.
 */
class ConfigurationBootstrapper extends AbstractBootstrapperModule
{
    /**
     * Load the custom files (tasks, events, ...).
     */
    public function bootstrapConfiguration()
    {
        $this->bootstrapDotenv();

        // Reload configuration
        $this->config->replace(
            $this->configurationLoader->getConfiguration()
        );

        $this->bootstrapPlugins();
    }

    /**
     * Load the .env file if necessary.
     */
    protected function bootstrapDotenv()
    {
        if (!$this->files->has($this->paths->getDotenvPath())) {
            return;
        }

        $path = $this->files->getAdapter()->applyPathPrefix($this->paths->getBasePath());
        $dotenv = new Dotenv($path);
        $dotenv->load();
    }

    /**
     * Load any configured plugins.
     */
    public function bootstrapPlugins()
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
            'bootstrapPlugins',
        ];
    }
}
