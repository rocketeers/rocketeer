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
 * The Mercury implementation of the VcsInterface.
 */
class Hg extends AbstractBinary implements VcsInterface
{
    /**
     * The core binary.
     *
     * @var string
     */
    protected $binary = 'hg';

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
        return $this->getCommand('identify -i');
    }

    /**
     * Get the current branch.
     *
     * @return string
     */
    public function currentBranch()
    {
        return $this->getCommand('branch');
    }

    /**
     * @return string
     */
    public function currentEndpoint()
    {
        return $this->getCommand('paths default');
    }

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

        $arguments = [
            $this->quote($repository->repository),
            '-b '.$repository->branch,
            $this->quote($destination),
        ];

        return $this->clone($arguments, $this->getCredentials());
    }

    /**
     * Resets the repository.
     *
     * @return string
     */
    public function reset()
    {
        return $this->getCommand('update --clean');
    }

    /**
     * Updates the repository.
     *
     * @return string
     */
    public function update()
    {
        return $this->pull();
    }

    /**
     * Checkout the repository's submodules.
     *
     * @return string|null
     */
    public function submodules()
    {
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the credentials required for cloning.
     *
     * @return array
     */
    private function getCredentials()
    {
        $options = ['--config ui.interactive' => 'no', '--config auth.x.prefix' => 'http://'];

        $repository = $this->credentials->getCurrentRepository();
        if ($user = $repository->username) {
            $options['--config auth.x.username'] = $user;
        }
        if ($pass = $repository->password) {
            $options['--config auth.x.password'] = $pass;
        }

        return $options;
    }
}
