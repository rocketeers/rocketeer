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

namespace Rocketeer\Services\Bootstrapper\Modules;

/**
 * Register the core paths with Rocketeer.
 */
class PathsBootstrapper extends AbstractBootstrapperModule
{
    /**
     * Bind paths to the container.
     */
    public function bootstrapPaths()
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
