<?php
namespace Rocketeer\Services\Connections;

class ConnectionHandle
{
    /**
     * @type string
     */
    public $connection;

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
     * @param string      $connection
     * @param string|null $server
     * @param string|null $stage
     * @param string|null $username
     */
    public function __construct($connection, $server = null, $stage = null, $username = null)
    {
        $this->connection = $connection;
        $this->server     = $server;
        $this->stage      = $stage;
        $this->username   = $username;
    }

    /**
     * @return string
     */
    public function toHandle()
    {
        $components = [$this->connection, $this->server, $this->stage];
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
}
