<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Connections\Credentials\Keys;

use Illuminate\Support\Str;

/**
 * Represents a repository's identity and its credentials.
 *
 * @property string endpoint
 * @property string branch
 * @property string username
 * @property string password
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class RepositoryKey extends AbstractKey
{
    /**
     * Whether the repository is public or needs credentials.
     *
     * @return bool
     */
    public function needsCredentials()
    {
        return Str::contains($this->endpoint, 'https://');
    }

    /**
     * Get a shorthand of the repository's name.
     *
     * @return string
     */
    public function getName()
    {
        $repository = $this->endpoint;
        $repository = preg_replace('#https?://(.+)\.com/(.+)/([^.]+)(\..+)?#', '$2/$3', $repository);

        return $repository;
    }

    /**
     * @return array
     */
    protected function getHandleComponents()
    {
        return [$this->username, $this->getName()];
    }
}
