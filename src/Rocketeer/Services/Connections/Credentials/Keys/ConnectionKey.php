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

namespace Rocketeer\Services\Connections\Credentials\Keys;

use Illuminate\Support\Arr;
use Rocketeer\Services\Connections\roles;

/**
 * Represents a connection's identity and its credential.
 *
 * @property string   name
 * @property int      server
 * @property string   stage
 * @property array    servers
 * @property string   host
 * @property string   username
 * @property string   password
 * @property string   key
 * @property string   keyphrase
 * @property string   agent
 * @property string   root_directory
 * @property string[] roles
 * @property bool     db_role
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class ConnectionKey extends AbstractKey
{
    /**
     * The global informations
     * about the connection.
     *
     * @var string[]
     */
    protected $informations = ['name', 'server', 'stage', 'servers'];

    /**
     * Get attributes from the credentials.
     *
     * @param string $name
     *
     * @return array|string
     */
    public function __get($name)
    {
        if (in_array($name, $this->informations, true)) {
            return $this->get($name);
        }

        return $this->getServerCredential($name);
    }

    /**
     * Chane a server credential.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        if (in_array($name, $this->informations, true)) {
            parent::__set($name, $value);
        } else {
            $this->attributes['servers'][$this->server][$name] = $value;
        }
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// CREDENTIALS ////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the credentials of the current server.
     *
     * @return array
     */
    public function getServerCredentials()
    {
        if (is_null($this->server)) {
            return [];
        }

        return Arr::get($this->servers, $this->server) ?: [];
    }

    /**
     * Get a credential in particular.
     *
     * @param string $credential
     *
     * @return string|array
     */
    public function getServerCredential($credential)
    {
        return Arr::get($this->getServerCredentials(), $credential);
    }

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// INFORMATIONS ////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param ConnectionKey|string $connection
     * @param int|null             $server
     *
     * @return bool
     */
    public function is($connection, $server = null)
    {
        if (is_string($connection)) {
            $name = $connection;
            $server = is_null($server) ? $this->server : $server;
        } else {
            $name = $connection->name;
            $server = $connection->server;
        }

        return $this->name === $name && $this->server === $server;
    }

    /**
     * Check if a connection is multiserver or not.
     *
     * @return bool
     */
    public function isMultiserver()
    {
        return count($this->servers) > 1;
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// HANDLES //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the components to compute the handle from.
     *
     * @return string[]
     */
    public function getHandleComponents()
    {
        $server = isset($this->servers[$this->server]['host']) ? $this->servers[$this->server]['host'] : $this->server;
        $components = !$this->isMultiserver() ? [$this->name, $this->stage] : [$this->name, $server, $this->stage];
        $components = array_filter($components, function ($value) {
            return $value !== null;
        });

        return $components;
    }

    /**
     * Get the long form of the handle.
     *
     * @return string
     */
    public function toLongHandle()
    {
        return $this->username.'@'.$this->toHandle();
    }

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// SERIALIZATION ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $connection = parent::toArray();
        $connection['multiserver'] = $this->isMultiserver();

        return $connection;
    }
}
