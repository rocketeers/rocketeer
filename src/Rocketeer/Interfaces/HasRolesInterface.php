<?php
namespace Rocketeer\Interfaces;

interface HasRolesInterface
{
    /**
     * @return array
     */
    public function getRoles();

    /**
     * @param array $roles
     *
     * @return void
     */
    public function setRoles(array $roles);

    /**
     * Add new roles to the entity
     *
     * @param array $roles
     *
     * @return void
     */
    public function addRoles(array $roles);

    /**
     * @param string $role
     *
     * @return boolean
     */
    public function hasRole($role);

    /**
     * Check if an entity is compatible with another
     *
     * @param HasRolesInterface $hasRoles
     *
     * @return boolean
     */
    public function isCompatibleWith(HasRolesInterface $hasRoles);
}
