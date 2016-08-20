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
 * Installs dependencies with NPM.
 */
class NpmStrategy extends AbstractDependenciesStrategy implements DependenciesStrategyInterface
{
    /**
     * The name of the binary.
     *
     * @var string
     */
    protected $binary = 'npm';

    /**
     * @var string
     */
    protected $description = 'Installs dependencies with NPM';

    /**
     * @var array
     */
    protected $options = [
        'shared_dependencies' => false,
        'flags.install' => '--no-progress',
    ];
}
