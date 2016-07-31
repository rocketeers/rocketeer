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
 * The Git implementation of the VcsInterface.
 */
class Git extends AbstractBinary implements VcsInterface
{
    /**
     * The core binary.
     *
     * @var string
     */
    protected $binary = 'git';

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
        return $this->revParse('HEAD');
    }

    /**
     * Get the current branch.
     *
     * @return string
     */
    public function currentBranch()
    {
        return $this->revParse('--abbrev-ref HEAD');
    }

    /**
     * @return string
     */
    public function currentEndpoint()
    {
        return $this->getCommand('remote get-url origin');
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
        $arguments = array_map([$this, 'quote'], [
            $repository->repository,
            $destination,
        ]);

        // Build flags
        $flags = ['--branch' => $repository->branch];
        if ($this->config->getContextually('vcs.shallow')) {
            $flags['--depth'] = 1;
        }

        return $this->clone($arguments, $flags);
    }

    /**
     * Resets the repository.
     *
     * @return string
     */
    public function reset()
    {
        return $this->getCommand('reset', [], ['--hard']);
    }

    /**
     * Updates the repository.
     *
     * @return string
     */
    public function update()
    {
        return $this->pull(null, ['--recurse-submodules']);
    }

    /**
     * Checkout the repository's submodules.
     *
     * @return string
     */
    public function submodules()
    {
        return $this->submodule('update', ['--init', '--recursive']);
    }
}
