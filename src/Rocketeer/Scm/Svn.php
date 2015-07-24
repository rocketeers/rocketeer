<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Scm;

use Illuminate\Support\Arr;
use Rocketeer\Abstracts\AbstractBinary;
use Rocketeer\Interfaces\ScmInterface;

/**
 * The Svn implementation of the ScmInterface.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 * @author Gasillo
 */
class Svn extends AbstractBinary implements ScmInterface
{
    /**
     * The core binary.
     *
     * @type string
     */
    public $binary = 'svn';

    ////////////////////////////////////////////////////////////////////
    ///////////////////////////// INFORMATIONS /////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Check if the SCM is available.
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
        return $this->getCommand('info | grep "Revision"');
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
        $branch     = $this->connections->getRepositoryBranch();
        $repository = $this->connections->getRepositoryEndpoint();
        $repository = rtrim($repository, '/').'/'.ltrim($branch, '/');
        $repository = preg_replace('#//[a-zA-Z0-9]+:?[a-zA-Z0-9]*@#', '//', $repository);

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
        $options     = ['--non-interactive' => null];
        $credentials = $this->connections->getRepositoryCredentials();

        // Build command
        if ($user = Arr::get($credentials, 'username')) {
            $options['--username'] = $user;
        }
        if ($pass = Arr::get($credentials, 'password')) {
            $options['--password'] = $pass;
        }

        return $options;
    }

    /**
     * Checkout the repository's submodules.
     *
     * @return string
     */
    public function submodules()
    {
        return;
    }
}
