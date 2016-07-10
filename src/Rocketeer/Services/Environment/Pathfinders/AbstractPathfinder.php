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

namespace Rocketeer\Services\Environment\Pathfinders;

use Rocketeer\Traits\ContainerAwareTrait;

abstract class AbstractPathfinder implements PathfinderInterface
{
    use ContainerAwareTrait;

    /**
     * Get a configured path.
     *
     * @param string $path
     *
     * @return string
     */
    public function getPath($path)
    {
        return $this->config->getContextually('paths.'.$path);
    }

    /**
     * Get the base path.
     *
     * @return string
     */
    public function getBasePath()
    {
        $base = $this->container->get('path.base') ? $this->container->get('path.base').'/' : '';
        $base = $this->unifySlashes($base);

        return $base;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Unify the slashes to the UNIX mode (forward slashes).
     *
     * @param string $path
     *
     * @return string
     */
    public function unifySlashes($path)
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * Unify paths to the local DS.
     *
     * @param string $path
     *
     * @return string
     */
    public function unifyLocalSlashes($path)
    {
        return preg_replace('#(/|\\\)#', DS, $path);
    }

    /**
     * Replace patterns in a folder path.
     *
     * @param string $path
     *
     * @return string
     */
    public function replacePatterns($path)
    {
        $base = $this->getBasePath();

        // Replace folder patterns
        return preg_replace_callback('/\{[a-z\.]+\}/', function ($match) use ($base) {
            $folder = substr($match[0], 1, -1);

            // Replace paths from the container
            if ($this->container->has($folder)) {
                $path = $this->container->get($folder);

                return str_replace($base, null, $this->unifySlashes($path));
            }

            // Replace paths from configuration
            if ($custom = $this->getPath($folder)) {
                return $custom;
            }

            return false;
        }, $path);
    }
}
