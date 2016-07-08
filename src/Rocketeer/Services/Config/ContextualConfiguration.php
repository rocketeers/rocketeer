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

use Rocketeer\Container;
use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;
use Rocketeer\Traits\HasLocator;

/**
 * @mixin Configuration
 */
class ContextualConfiguration
{
    use HasLocator;

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

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->configuration->$name(...$arguments);
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

        if ($contextual = $this->getForContext($option, 'stages', $original, $connectionKey)) {
            return $contextual;
        }

        if ($contextual = $this->getForContext($option, 'connections', $original, $connectionKey)) {
            return $contextual;
        }

        if ($contextual = $this->getForContext($option, 'servers', $original)) {
            return $contextual;
        }

        return $original;
    }

    /**
     * Get a contextual option.
     *
     * @param string             $option
     * @param string             $type          [stage,connection]
     * @param string|array|null  $original
     * @param ConnectionKey|null $connectionKey
     *
     * @return array|\Closure|string
     */
    protected function getForContext($option, $type, $original = null, ConnectionKey $connectionKey = null)
    {
        $connectionKey = $connectionKey ?: $this->connections->getCurrentConnectionKey();

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
                $contextual = sprintf('%s', $option);
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
