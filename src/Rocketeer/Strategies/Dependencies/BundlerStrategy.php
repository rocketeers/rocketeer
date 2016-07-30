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

namespace Rocketeer\Strategies\Dependencies;

/**
 * Installs dependencies with Bundler.
 */
class BundlerStrategy extends AbstractDependenciesStrategy implements DependenciesStrategyInterface
{
    /**
     * The name of the binary.
     *
     * @var string
     */
    protected $binary = 'bundler';

    /**
     * @var string
     */
    protected $description = 'Installs dependencies with Bundler';
}
