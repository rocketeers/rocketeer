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

use Illuminate\Support\Arr;
use Rocketeer\Abstracts\Strategies\AbstractDependenciesStrategy;
use Rocketeer\Interfaces\Strategies\DependenciesStrategyInterface;

class BowerStrategy extends AbstractDependenciesStrategy implements DependenciesStrategyInterface
{
    /**
     * @type string
     */
    protected $description = 'Installs dependencies with Bower';

    /**
     * The name of the binary
     *
     * @type string
     */
    protected $binary = 'bower';

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// COMMANDS //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Install the dependencies
     *
     * @return bool
     */
    public function install()
    {
        $this->shareDependenciesFolder();

        return $this->manager->runForApplication('install', [], $this->getInstallationOptions('install'));
    }

    /**
     * Update the dependencies
     *
     * @return boolean
     */
    public function update()
    {
        return $this->manager->runForApplication('update', [], $this->getInstallationOptions('update'));
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the options to run Bower with
     *
     * @param string $command
     *
     * @return array
     */
    protected function getInstallationOptions($command)
    {
        $flags       = $this->getFlags($command);
        $credentials = $this->connections->getServerCredentials();
        if (Arr::get($credentials, 'username') === 'root') {
            return array_merge($flags, ['--allow-root' => null]);
        }

        return $flags;
    }
}
