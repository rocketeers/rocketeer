<?php
namespace Rocketeer\Interfaces;

interface IdentifierInterface
{
    /**
     * Get a global identifier for this entity
     *
     * @return string
     */
    public function getIdentifier();
}
