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

namespace Rocketeer\Services\Connections\Credentials;

use Rocketeer\Services\Connections\Credentials\Keychains\ConnectionsKeychain;
use Rocketeer\Services\Connections\Credentials\Keychains\RepositoriesKeychain;
use Rocketeer\Traits\ContainerAware;

class CredentialsHandler
{
    use ContainerAware;
    use RepositoriesKeychain;
    use ConnectionsKeychain;
}
