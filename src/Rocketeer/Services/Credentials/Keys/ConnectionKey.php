<?php
namespace Rocketeer\Services\Credentials\Keys;

use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;
use Rocketeer\Services\Connections\roles;

/**
 * Represents a connection's identity and its credential.
 *
 * @property string  name
 * @property integer server
 * @property string  stage
 * @property array   servers
 * @property string  host
 * @property string  username
 * @property string  password
 * @property string  key
 * @property string  keyphrase
 * @property string  agent
 * @property boolean db_role
 * @property roles   array
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class ConnectionKey extends Fluent
{
    /**
     * The global informations
     * about the connection.
     *
     * @type string[]
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
        if (in_array($name, $this->informations)) {
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
        if (in_array($name, $this->informations)) {
            return parent::__set($name, $value);
        }

        $this->attributes['servers'][$this->server][$name] = $value;
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
        return Arr::get($this->servers, $this->server);
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
     * Check if a connection is multiserver or not.
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

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// SERIALIZATION ///////////////////////////
    //////////////////////////////////////////////////////////////////////

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
        $connection                = parent::toArray();
        $connection['multiserver'] = $this->isMultiserver();

        return $connection;
    }
}
