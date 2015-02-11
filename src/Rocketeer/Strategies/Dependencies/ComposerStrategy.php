<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Strategies\Dependencies;

use Rocketeer\Abstracts\Strategies\AbstractDependenciesStrategy;
use Rocketeer\Interfaces\Strategies\DependenciesStrategyInterface;

class ComposerStrategy extends AbstractDependenciesStrategy implements DependenciesStrategyInterface
{
    protected $options = array(
        'shared_dependencies' => false,
        'flags'               => array(
            'install' => ['--no-interaction' => null, '--no-dev' => null, '--prefer-dist' => null],
        ),
    );

    /**
     * @type string
     */
    protected $description = 'Installs dependencies with Composer';

    /**
     * The name of the binary
     *
     * @type string
     */
    protected $binary = 'composer';
}
