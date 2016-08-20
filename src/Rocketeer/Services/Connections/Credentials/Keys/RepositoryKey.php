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

namespace Rocketeer\Services\Connections\Credentials\Keys;

use Illuminate\Support\Str;

/**
 * Represents a repository's identity and its credentials.
 */
class RepositoryKey extends AbstractKey
{
    /**
     * @var string
     */
    public $endpoint;

    /**
     * @var string
     */
    public $branch;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    /**
     * Whether the repository is public or needs credentials.
     *
     * @return bool
     */
    public function needsCredentials()
    {
        return Str::contains($this->repository, 'https://');
    }

    /**
     * Get a shorthand of the repository's name.
     *
     * @return string
     */
    public function getName()
    {
        return preg_replace('#(git@|https?:\/\/)(.+)\.com[:\/](.+)\/([^.]+)(\..+)?#', '$3/$4', $this->repository);
    }

    /**
     * @return array
     */
    protected function getAttributes()
    {
        return [$this->username, $this->getName()];
    }
}
