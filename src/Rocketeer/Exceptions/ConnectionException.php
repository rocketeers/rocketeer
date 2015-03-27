<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Exceptions;

use Exception;
use Rocketeer\Interfaces\CredentialsExceptionInterface;
use Rocketeer\Traits\Exceptions\WithCredentials;

/**
 * Exception when Rocketeer can't connect to a server.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class ConnectionException extends Exception implements CredentialsExceptionInterface
{
    use WithCredentials;
}
