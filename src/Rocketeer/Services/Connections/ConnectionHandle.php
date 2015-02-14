<?php
namespace Rocketeer\Services\Connections;

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
     * @type boolean
     */
    public $multiserver = false;

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

    /**
     * @param ConnectionHandle $connection
     *
     * @return boolean
     */
    public function is(ConnectionHandle $connection)
    {
        return $this->toArray() === $connection->toArray();
    }

    /**
     * @return string
     */
    public function toHandle()
    {
        $components = $this->multiserver ? [$this->name, $this->stage] : [$this->name, $this->server, $this->stage];
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
            'multiserver' => $this->multiserver,
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
