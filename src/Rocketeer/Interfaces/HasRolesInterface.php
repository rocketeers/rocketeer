<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Interfaces;

interface HasRolesInterface
{
    /**
     * @return array
     */
    public function getRoles();

    /**
     * @param array $roles
     */
    public function setRoles(array $roles);

    /**
     * Add new roles to the entity.
     *
     * @param array $roles
     */
    public function addRoles(array $roles);

    /**
     * @param string $role
     *
     * @return boolean
     */
    public function hasRole($role);

    /**
     * Check if an entity is compatible with another.
     *
     * @param HasRolesInterface $hasRoles
     *
     * @return boolean
     */
    public function isCompatibleWith(HasRolesInterface $hasRoles);
}
