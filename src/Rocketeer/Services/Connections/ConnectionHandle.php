<?php
namespace Rocketeer\Services\Connections;

use Illuminate\Support\Arr;
use Illuminate\Support\Contracts\ArrayableInterface;
use JsonSerializable;

/**
 * @property string  host
 * @property string  username
 * @property string  password
 * @property string  key
 * @property string  keyphrase
 * @property string  agent
 * @property boolean db_role
 * @property roles   array
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class ConnectionHandle implements ArrayableInterface, JsonSerializable
{
    /**
     * @type string
     */
    public $name;

    /**
     * @type string
     */
    public $server;

    /**
     * @type string
     */
    public $stage;

    /**
     * The credentials of the various servers
     *
     * @type array
     */
    public $servers = [];

    /**
     * @param string      $name
     * @param string|null $server
     * @param string|null $stage
     */
    public function __construct($name, $server = null, $stage = null)
    {
        $this->name   = $name;
        $this->server = $server;
        $this->stage  = $stage;
    }

    /**
     * Get attributes from the credentials
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
     * Chane a server credential
     *
     * @param string $name
     * @param mixed  $value
     */
    function __set($name, $value)
    {
        $this->servers[$this->server][$name] = $value;
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// CREDENTIALS ////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the credentials of the current server
     *
     * @return array
     */
    public function getServerCredentials()
    {
        return Arr::get($this->servers, $this->server);
    }

    /**
     * Get a credential in particular
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
     * @param ConnectionHandle|string $connection
     *
     * @return boolean
     */
    public function is($connection)
    {
        // If we only passed the name, check this
        if (is_string($connection)) {
            return $this->name === $connection;
        }

        return $this->name === $connection->name && $this->server === $connection->server;
    }

    /**
     * Check if a connection is multiserver or not
     *
     * @return boolean
     */
    public function isMultiserver()
    {
        return count($this->servers) > 1;
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// HANDLES //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return string
     */
    public function toHandle()
    {
        $server     = Arr::get($this->servers, $this->server.'.host', $this->server);
        $components = !$this->isMultiserver() ? [$this->name, $this->stage] : [$this->name, $server, $this->stage];
        $components = array_filter($components, function ($value) {
            return $value !== null;
        });

        return implode('/', $components);
    }

    /**
     * @return string
     */
    public function toLongHandle()
    {
        return $this->username.'@'.$this->toHandle();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toHandle();
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'name'        => $this->name,
            'server'      => $this->server,
            'stage'       => $this->stage,
            'username'    => $this->username,
            'servers'     => $this->servers,
            'multiserver' => $this->isMultiserver(),
        );
    }

    /**
     * Get the instance as JSON
     *
     * @return string
     */
    public function jsonSerialize()
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
