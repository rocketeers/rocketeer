<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Interfaces;

/**
 * The interface for all SCM implementations.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
interface ScmInterface
{
    /**
     * Get the current binary name.
     *
     * @return string
     */
    public function getBinary();

    /**
     * Check if the SCM is available.
     *
     * @return string
     */
    public function check();

    /**
     * Get the current state.
     *
     * @return string
     */
    public function currentState();

    /**
     * Get the current branch.
     *
     * @return string
     */
    public function currentBranch();

    /**
     * Clone a repository.
     *
     * @param string $destination
     *
     * @return string
     */
    public function checkout($destination);

    /**
     * Resets the repository.
     *
     * @return string
     */
    public function reset();

    /**
     * Updates the repository.
     *
     * @return string
     */
    public function update();

    /**
     * Checkout the repository's submodules.
     *
     * @return string
     */
    public function submodules();
}
