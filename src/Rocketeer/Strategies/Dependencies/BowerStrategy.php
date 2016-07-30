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

use Illuminate\Support\Arr;

/**
 * Installs dependencies with Bower.
 */
class BowerStrategy extends AbstractDependenciesStrategy implements DependenciesStrategyInterface
{
    /**
     * The name of the binary.
     *
     * @var string
     */
    protected $binary = 'bower';

    /**
     * @var string
     */
    protected $description = 'Installs dependencies with Bower';

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the options to run Bower with.
     *
     * @param string $command
     *
     * @return array
     */
    protected function getInstallationOptions($command)
    {
        $flags = (array) $this->getFlags($command);
        $credentials = $this->credentials->getServerCredentials();
        if (Arr::get($credentials, 'username') === 'root') {
            return array_merge($flags, ['--allow-root' => null]);
        }

        return $flags;
    }
}
