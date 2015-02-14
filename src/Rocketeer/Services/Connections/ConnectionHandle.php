<?php
namespace Rocketeer\Services\Connections;

use Illuminate\Support\Arr;
use Illuminate\Support\Contracts\ArrayableInterface;
use JsonSerializable;

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
     * @type string
     */
    public $username;

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
     * @param string|null $username
     */
    public function __construct($name, $server = null, $stage = null, $username = null)
    {
        $this->name     = $name;
        $this->server   = $server;
        $this->stage    = $stage;
        $this->username = $username;
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
        return json_decode($this->toArray());
    }
}
