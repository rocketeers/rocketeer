<?php
namespace Rocketeer\Services\Connections\Connections;

use League\Flysystem\Filesystem;
use Rocketeer\Interfaces\HasRolesInterface;
use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;
use Rocketeer\Traits\Properties\HasRoles;

abstract class AbstractConnection extends Filesystem implements ConnectionInterface, HasRolesInterface
{
    use HasRoles;

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * @var bool
     */
    protected $current = false;

    /**
     * @var ConnectionKey
     */
    protected $connectionKey;

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return boolean
     */
    public function isCurrent()
    {
        return $this->current;
    }

    /**
     * @param boolean $current
     */
    public function setCurrent($current)
    {
        $this->current = $current;
    }

    /**
     * @return ConnectionKey
     */
    public function getConnectionKey()
    {
        return clone $this->connectionKey;
    }

    /**
     * @param ConnectionKey $connectionKey
     */
    public function setConnectionKey(ConnectionKey $connectionKey)
    {
        $this->connectionKey = $connectionKey;
    }
}
