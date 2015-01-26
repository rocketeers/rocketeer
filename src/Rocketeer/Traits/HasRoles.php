<?php
namespace Rocketeer\Traits;

use Rocketeer\Interfaces\HasRolesInterface;

trait HasRoles
{
    /**
     * @type array
     */
    protected $roles = [];

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * Add new roles to the entity
     *
     * @param array $roles
     */
    public function addRoles(array $roles)
    {
        $this->roles = array_merge($this->roles, $roles);
    }

    /**
     * @param string $role
     *
     * @return boolean
     */
    public function hasRole($role)
    {
        return in_array($role, $this->roles);
    }

    /**
     * Check if an entity is compatible with another
     *
     * @param HasRolesInterface $hasRoles
     *
     * @return boolean
     */
    public function isCompatibleWith(HasRolesInterface $hasRoles)
    {
        $roles  = $hasRoles->getRoles();
        $filled = array_intersect($this->roles, $roles);

        return count($filled) === count($roles);
    }
}
