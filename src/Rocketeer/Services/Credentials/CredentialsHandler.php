<?php
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
