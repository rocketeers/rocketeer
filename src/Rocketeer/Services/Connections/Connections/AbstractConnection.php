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

namespace Rocketeer\Services\Connections\Connections;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;
use Rocketeer\Services\Roles\HasRolesInterface;
use Rocketeer\Services\Roles\HasRolesTrait;

abstract class AbstractConnection extends Filesystem implements ConnectionInterface, HasRolesInterface
{
    use HasRolesTrait;

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
     * @var array
     */
    protected $roles = [];

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return bool
     */
    public function isCurrent()
    {
        return $this->current;
    }

    /**
     * @param bool $current
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

    /**
     * @param AdapterInterface $adapter
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }
}
