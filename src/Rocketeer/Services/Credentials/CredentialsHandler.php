<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Credentials;

use Rocketeer\Services\Credentials\Keychains\ConnectionsKeychain;
use Rocketeer\Services\Credentials\Keychains\RepositoriesKeychain;
use Rocketeer\Traits\HasLocator;

class CredentialsHandler
{
    use HasLocator;
    use RepositoriesKeychain;
    use ConnectionsKeychain;
}
