<?php
namespace Rocketeer\Services\Credentials\Keys;

use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

/**
 * Represents a repository's identity and its credentials
 *
 * @property string endpoint
 * @property string branch
 * @property string username
 * @property string password
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class RepositoryKey extends Fluent
{
    /**
     * Whether the repository is public or needs credentials
     *
     * @return boolean
     */
    public function needsCredentials()
    {
        return Str::contains($this->endpoint, 'https://');
    }

    /**
     * Get a shorthand of the repository's name
     *
     * @return string
     */
    public function getName()
    {
        $repository = $this->endpoint;
        $repository = preg_replace('#https?://(.+)\.com/(.+)/([^.]+)(\..+)?#', '$2/$3', $repository);

        return $repository;
    }
}
