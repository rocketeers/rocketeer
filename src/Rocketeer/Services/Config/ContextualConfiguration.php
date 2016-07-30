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

namespace Rocketeer\Services\Config;

use Closure;
use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;
use Rocketeer\Services\Container\Container;
use Rocketeer\Traits\ContainerAwareTrait;

/**
 * Gets configuration relative to the current connection/plugin.
 *
 * @mixin Configuration
 */
class ContextualConfiguration implements ConfigurationInterface
{
    use ContainerAwareTrait;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @param Container     $container
     * @param Configuration $configuration
     */
    public function __construct(Container $container, Configuration $configuration)
    {
        $this->container = $container;
        $this->configuration = $configuration;
    }

    ////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////// BASE CONFIGURATION //////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        return $this->configuration->get($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        return $this->configuration->set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function replace(array $items)
    {
        return $this->configuration->replace($items);
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->configuration->all();
    }

    ////////////////////////////////////////////////////////////////////////////////
    //////////////////////////// CONTEXTUAL CONFIGURATION //////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Get an option for a plugin.
     *
     * @param string      $plugin
     * @param string|null $option
     *
     * @return mixed
     */
    public function getPluginOption($plugin, $option = null)
    {
        $option = $option ? '.'.$option : '';

        return $this->config->get('plugins.config.'.$plugin.$option);
    }

    /**
     * Get an option from Rocketeer's config file.
     *
     * @param string             $option
     * @param ConnectionKey|null $connectionKey
     *
     * @return array|Closure|string
     */
    public function getContextually($option, ConnectionKey $connectionKey = null)
    {
        $original = $this->configuration->get($option);
        $connectionKey = $connectionKey ?: $this->connections->getCurrentConnectionKey();

        $contexts = ['stages', 'connections', 'servers'];
        foreach ($contexts as $context) {
            if ($contextual = $this->getForContext($option, $context, $original, $connectionKey)) {
                return $contextual;
            }
        }

        return $original;
    }

    /**
     * Get a contextual option.
     *
     * @param string             $option
     * @param string             $type
     * @param string|array|null  $original
     * @param ConnectionKey|null $connectionKey
     *
     * @return array|Closure|string
     */
    protected function getForContext($option, $type, $original, ConnectionKey $connectionKey)
    {
        // Switch context
        switch ($type) {
            case 'servers':
                $contextual = sprintf('connections.%s.servers.%d.config.%s', $connectionKey->name, $connectionKey->server, $option);
                break;

            case 'stages':
                $contextual = sprintf('on.stages.%s.%s', $connectionKey->stage, $option);
                break;

            case 'connections':
                $contextual = sprintf('on.connections.%s.%s', $connectionKey->name, $option);
                break;

            default:
                $contextual = $option;
                break;
        }

        // Merge with defaults
        $value = $this->configuration->get($contextual);
        if (is_array($value) && $original) {
            $value = array_replace($original, $value);
        }

        return $value;
    }
}
