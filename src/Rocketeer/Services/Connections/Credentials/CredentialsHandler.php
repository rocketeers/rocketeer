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

use League\Container\ContainerAwareInterface;
use Rocketeer\Services\Connections\Credentials\Keys\RepositoryKey;
use Rocketeer\Services\Connections\Credentials\Modules\ConnectionsKeychain;
use Rocketeer\Services\Connections\Credentials\Modules\RepositoriesKeychain;
use Rocketeer\Services\Modules\ModulableInterface;
use Rocketeer\Services\Modules\ModulableTrait;
use Rocketeer\Traits\ContainerAwareTrait;

/**
 * @mixin ConnectionsKeychain
 * @mixin RepositoriesKeychain
 *
 * @method RepositoryKey getCurrentRepository()
 */
class CredentialsHandler implements ModulableInterface, ContainerAwareInterface
{
    use ModulableTrait;
    use ContainerAwareTrait;
}
