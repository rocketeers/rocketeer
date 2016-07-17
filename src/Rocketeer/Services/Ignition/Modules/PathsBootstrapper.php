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

namespace Rocketeer\Services\Ignition\Modules;

class PathsBootstrapper extends AbstractBootstrapperModule
{
    /**
     * Bind paths to the container.
     */
    public function bootstrapPaths()
    {
        $this->bindBase();
//        $this->bindConfiguration();
    }

    /**
     * Bind the base path to the Container.
     */
    protected function bindBase()
    {
        if ($this->container->has('path.base')) {
            return;
        }

        $this->container->add('path.base', getcwd());
    }

    /**
     * @return string[]
     */
    public function getProvided()
    {
        return [
            'bootstrapPaths',
        ];
    }
}
