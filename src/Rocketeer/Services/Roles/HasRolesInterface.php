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

namespace Rocketeer\Services\Roles;

/**
 * A class that can have roles and compare
 * them with a require list.
 */
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
     * @return bool
     */
    public function hasRole($role);

    /**
     * Check if an entity is compatible with another.
     *
     * @param HasRolesInterface $hasRoles
     *
     * @return bool
     */
    public function isCompatibleWith(HasRolesInterface $hasRoles);
}
