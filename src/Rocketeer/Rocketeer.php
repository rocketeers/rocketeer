<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer;

use Rocketeer\Services\Credentials\Keys\ConnectionKey;
use Rocketeer\Traits\HasLocator;

/**
 * Handles interaction between the User provided informations
 * and the various Rocketeer components.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Rocketeer
{
    use HasLocator;

    /**
     * The Rocketeer version.
     *
     * @type string
     */
    const VERSION = '3.0.0';

    /**
     * The specific commit built.
     */
    const COMMIT = '@commit@';

    /**
     * Global local mode.
     *
     * @type bool
     */
    protected $local = false;

    /**
     * Returns what stage Rocketeer thinks he's in.
     *
     * @param string      $application
     * @param string|null $path
     *
     * @return string|false
     */
    public static function getDetectedStage($application = 'application', $path = null)
    {
        $current = $path ?: realpath(__DIR__);
        preg_match('/'.$application.'\/([a-zA-Z0-9_-]+)\/releases\/([0-9]{14})/', $current, $matches);

        return isset($matches[1]) ? $matches[1] : false;
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// LOCAL ////////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return bool
     */
    public function isLocal()
    {
        return $this->local;
    }

    /**
     * @param bool $local
     */
    public function setLocal($local)
    {
        $this->local = $local;
    }

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// CONFIGURATION ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the name of the application to deploy.
     *
     * @return string
     */
    public function getApplicationName()
    {
        return $this->config->get('application_name');
    }

    /**
     * Get an option from Rocketeer's config file.
     *
     * @param string        $option
     * @param ConnectionKey $connectionKey
     *
     * @return array|Closure|string
     */
    public function getOption($option, ConnectionKey $connectionKey = null)
    {
        $original = $this->config->get($option);

        if ($contextual = $this->getContextualOption($option, 'stages', $original, $connectionKey)) {
            return $contextual;
        }

        if ($contextual = $this->getContextualOption($option, 'connections', $original, $connectionKey)) {
            return $contextual;
        }

        if ($contextual = $this->getContextualOption($option, 'servers', $original)) {
            return $contextual;
        }

        return $original;
    }

    /**
     * Get a contextual option.
     *
     * @param string            $option
     * @param string            $type [stage,connection]
     * @param string|array|null $original
     * @param ConnectionKey     $connectionKey
     *
     * @return array|\Closure|string
     */
    protected function getContextualOption($option, $type, $original = null, ConnectionKey $connectionKey = null)
    {
        $connectionKey = $connectionKey ?: $this->connections->getCurrentConnection();

        // Switch context
        switch ($type) {
            case 'servers':
                $contextual = sprintf('connections.%s.servers.%d.config.%s', $current->name, $current->server, $option);
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
        $value = $this->config->get($contextual);
        if (is_array($value) && $original) {
            $value = array_replace($original, $value);
        }

        return $value;
    }
}
