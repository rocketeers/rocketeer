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

/**
 * Represents a connection's identity and its credential.
 *
 * @property string   $host
 * @property string   $username
 * @property string   $password
 * @property string   $key
 * @property string   $keyphrase
 * @property bool     $agent
 * @property string   $root_directory
 * @property string[] $roles
 */
class ConnectionKey extends AbstractKey
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $server;

    /**
     * @var string
     */
    public $stage;

    /**
     * @var array
     */
    public $servers;

    /**
     * Get a server credential.
     *
     * @param string $name
     *
     * @return array|string
     */
    public function __get($name)
    {
        return $this->getServerCredential($name);
    }

    /**
     * Change a server credential.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        $this->servers[$this->server][$name] = $value;
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

        return isset($this->servers[$this->server]) ? $this->servers[$this->server] : [];
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
        $credentials = $this->getServerCredentials();

        return isset($credentials[$credential]) ? $credentials[$credential] : null;
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
     * @return bool
     */
    public function isFtp()
    {
        return mb_strpos($this->host, 'ftp.') === 0;
    }

    /**
     * Check if a connection is multiServer or not.
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
    public function getAttributes()
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

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $connection = parent::toArray();
        $connection['multiserver'] = $this->isMultiserver();

        return $connection;
    }
}
