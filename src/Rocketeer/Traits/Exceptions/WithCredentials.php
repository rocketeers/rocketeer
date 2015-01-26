<?php
namespace Rocketeer\Traits\Exceptions;

trait WithCredentials
{
    /**
     * Set the credentials that failed to connect
     *
     * @param array $credentials
     */
    public function setCredentials(array $credentials)
    {
        $this->message .= PHP_EOL.'With credentials:'.PHP_EOL.json_encode($credentials, JSON_PRETTY_PRINT);
    }
}
