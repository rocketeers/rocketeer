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

namespace Rocketeer\Services\Connections\Credentials\Modules;

use Illuminate\Support\Arr;
use Rocketeer\Services\Connections\Credentials\Keys\RepositoryKey;
use Rocketeer\Services\Modules\AbstractModule;

/**
 * Finds credentials and informations about repositories.
 */
class RepositoriesKeychain extends AbstractModule
{
    /**
     * Get the current repository in use.
     *
     * @return RepositoryKey
     */
    public function getCurrentRepository()
    {
        $credentials = $this->getRepositoryCredentials();
        $credentials['repository'] = $this->getRepositoryEndpoint();
        $credentials['branch'] = $this->getRepositoryBranch();

        return new RepositoryKey($credentials);
    }

    /**
     * Get the credentials for the repository.
     *
     * @return array
     */
    protected function getRepositoryCredentials()
    {
        $config = (array) $this->config->getContextually('vcs');
        $credentials = (array) $this->localStorage->get('credentials');

        return array_merge($config, $credentials);
    }

    /**
     * Get the URL to the Git repository.
     *
     * @return string
     */
    protected function getRepositoryEndpoint()
    {
        // Get credentials
        $repository = $this->getRepositoryCredentials();
        $username = Arr::get($repository, 'username');
        $password = Arr::get($repository, 'password');
        $repository = Arr::get($repository, 'repository');

        // Add credentials if possible
        if ($username || $password) {
            // Encore parameters
            $username = urlencode($username);
            $password = urlencode($password);

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
     * Get the repository branch to use.
     *
     * @return string
     */
    protected function getRepositoryBranch()
    {
        // If we passed a branch, use it
        if ($branch = $this->getOption('branch')) {
            return $branch;
        }

        // Get branch from config, else compute the fallback
        $branch = $this->config->getContextually('vcs.branch');
        if (!$branch) {
            $fallback = $this->bash->on('local', function () {
                return $this->vcs->runSilently('currentBranch');
            });

            $fallback = $fallback ?: 'master';
            $branch = trim($fallback);
        }

        return $branch;
    }

    /**
     * @return string[]
     */
    public function getProvided()
    {
        return [
            'getCurrentRepository',
        ];
    }
}
