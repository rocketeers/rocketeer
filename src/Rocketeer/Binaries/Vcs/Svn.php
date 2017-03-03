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

namespace Rocketeer\Binaries\Vcs;

use Rocketeer\Binaries\AbstractBinary;

/**
 * The Svn implementation of the VcsInterface.
 *
 *
 * @author Gasillo
 */
class Svn extends AbstractBinary implements VcsInterface
{
    /**
     * The core binary.
     *
     * @var string
     */
    public $binary = 'svn';

    ////////////////////////////////////////////////////////////////////
    ///////////////////////////// INFORMATIONS /////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Check if the VCS is available.
     *
     * @return string
     */
    public function check()
    {
        return $this->getCommand('--version');
    }

    /**
     * Get the current state.
     *
     * @return string
     */
    public function currentState()
    {
        return $this->getInformationAttribute('revision');
    }

    /**
     * @return string
     */
    public function currentEndpoint()
    {
        return $this->getInformationAttribute('url');
    }

    /**
     * Get the current branch.
     *
     * @return string
     */
    public function currentBranch()
    {
        return 'echo trunk';
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// ACTIONS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Clone a repository.
     *
     * @param string $destination
     *
     * @return string
     */
    public function checkout($destination)
    {
        $repository = $this->credentials->getCurrentRepository();
        $branch = $repository->branch;
        $repository = $repository->repository;
        $repository = rtrim($repository, '/').'/'.ltrim($branch, '/');
        $repository = preg_replace('#//[a-zA-Z0-9.]+:?[a-zA-Z0-9]*@#', '//', $repository);

        return $this->co([$repository, $destination], $this->getCredentials());
    }

    /**
     * Resets the repository.
     *
     * @return string
     */
    public function reset()
    {
        $command = sprintf('status -q | grep -v \'^[~XI ]\' | awk \'{print $2;}\' | xargs --no-run-if-empty %s revert', $this->binary);

        return $this->getCommand($command);
    }

    /**
     * Updates the repository.
     *
     * @return string
     */
    public function update()
    {
        return $this->up([], $this->getCredentials());
    }

    /**
     * Return credential options.
     *
     * @return array|array<string,null>
     */
    protected function getCredentials()
    {
        $options = ['--non-interactive' => null];
        $repository = $this->credentials->getCurrentRepository();

        // Build command
        if ($user = $repository->username) {
            $options['--username'] = $user;
        }
        if ($pass = $repository->password) {
            $options['--password'] = $pass;
        }

        return $options;
    }

    /**
     * Checkout the repository's submodules.
     *
     * @return string|null
     */
    public function submodules()
    {
    }

    ////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////// HELPERS ////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * @param string $attribute
     *
     * @return string
     */
    protected function getInformationAttribute($attribute)
    {
        return $this->getCommand('info --show-item '.$attribute);
    }
}
