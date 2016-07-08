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

use Rocketeer\Strategies\AbstractDependenciesStrategy;

class ComposerStrategy extends AbstractDependenciesStrategy implements DependenciesStrategyInterface
{
    protected $options = [
        'shared_dependencies' => false,
        'flags' => [
            'install' => ['--no-interaction' => null, '--no-dev' => null, '--prefer-dist' => null],
        ],
    ];

    /**
     * @var string
     */
    protected $description = 'Installs dependencies with Composer';

    /**
     * The name of the binary.
     *
     * @var string
     */
    protected $binary = 'composer';
}
