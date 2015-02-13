<?php
namespace Rocketeer\Interfaces;

/**
 * An exception that can dump credentials
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
interface CredentialsExceptionInterface
{
    /**
     * Set the credentials that failed to connect
     *
     * @param array $credentials
     *
     * @return void
     */
    public function setCredentials(array $credentials);
}
