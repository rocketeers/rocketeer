<?php
namespace Rocketeer\Services\Credentials\Keychains;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Finds credentials and informations about repositories
 *
 * @mixin \Rocketeer\Services\Credentials\CredentialsHandler
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait RepositoriesKeychain
{
    /**
     * Whether the repository used is using SSH or HTTPS
     *
     * @return boolean
     */
    public function repositoryNeedsCredentials()
    {
        return Str::contains($this->getRepositoryEndpoint(), 'https://');
    }

    /**
     * Get the credentials for the repository
     *
     * @return array
     */
    public function getRepositoryCredentials()
    {
        $config      = (array) $this->rocketeer->getOption('scm');
        $credentials = (array) $this->localStorage->get('credentials');

        return array_merge($config, $credentials);
    }

    /**
     * Get the URL to the Git repository
     *
     * @return string
     */
    public function getRepositoryEndpoint()
    {
        // Get credentials
        $repository = $this->getRepositoryCredentials();
        $username   = Arr::get($repository, 'username');
        $password   = Arr::get($repository, 'password');
        $repository = Arr::get($repository, 'repository');

        // Add credentials if possible
        if ($username || $password) {

            // Build credentials chain
            $credentials = $password ? $username.':'.$password : $username;
            $credentials .= '@';

            // Add them in chain
            $repository = preg_replace('#https://(.+)@#', 'https://', $repository);
            $repository = str_replace('https://', 'https://'.$credentials, $repository);
        }

        return $repository;
    }

    /**
     * Get the repository branch to use
     *
     * @return string
     */
    public function getRepositoryBranch()
    {
        // If we passed a branch, use it
        if ($branch = $this->getOption('branch')) {
            return $branch;
        }

        // Compute the fallback branch
        $fallback = $this->bash->onLocal(function () {
            return $this->scm->runSilently('currentBranch');
        });
        $fallback = $fallback ?: 'master';
        $fallback = trim($fallback);
        $branch   = $this->rocketeer->getOption('scm.branch') ?: $fallback;

        return $branch;
    }

    /**
     * Get repository name to use
     *
     * @return string
     */
    public function getRepositoryName()
    {
        $repository = $this->getRepositoryEndpoint();
        $repository = preg_replace('#https?://(.+)\.com/(.+)/([^.]+)(\..+)?#', '$2/$3', $repository);

        return $repository;
    }
}
