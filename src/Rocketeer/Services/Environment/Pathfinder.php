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

namespace Rocketeer\Services\Environment;

use League\Container\ContainerAwareInterface;
use Rocketeer\Services\Environment\Modules\ApplicationPathfinder;
use Rocketeer\Services\Environment\Modules\HomePathfinder;
use Rocketeer\Services\Environment\Modules\ServerPathfinder;
use Rocketeer\Services\Modules\ModulableInterface;
use Rocketeer\Services\Modules\ModulableTrait;
use Rocketeer\Traits\ContainerAwareTrait;

/**
 * Locates folders and paths on the server and locally.
 *
 * @mixin ApplicationPathfinder
 * @mixin HomePathfinder
 * @mixin ServerPathfinder
 *
 * @method string getConfigurationPath()
 */
class Pathfinder implements ModulableInterface, ContainerAwareInterface
{
    use ModulableTrait;
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
            $folder = mb_substr($match[0], 1, -1);

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

    /**
     * Get a relative path from one file or directory to another.
     *
     * If $from is a path to a file (i.e. does not end with a "/"), the
     * returned path will be relative to its parent directory.
     *
     * @param string $from
     * @param string $to
     *
     * @return string
     */
    public function computeRelativePathBetween($from, $to)
    {
        $from = $this->explodePath($from);
        $to = $this->explodePath($to);

        // Skip the common path prefix
        foreach ($from as $key => $component) {
            if (isset($to[$key]) && $to[$key] === $component) {
                unset($from[$key], $to[$key]);
            }
        }

        // Compute new realpath
        $relativePath = implode('/', $to);
        $relativePath = str_repeat('../', count($from) - 1).$relativePath;
        $relativePath = trim($relativePath, '/');

        return $relativePath;
    }

    /**
     * @param string $path
     *
     * @return array
     */
    protected function explodePath($path)
    {
        $path = $this->unifySlashes($path);
        $path = str_replace('//', '/', $path);

        // Resolve ../ calls
        $parent = '#/?([^\/]+)/\.\.#';
        while (preg_match($parent, $path)) {
            $path = preg_replace($parent, '', $path);
            $path = trim($path, '/');
        }

        $path = explode('/', $path);
        $path = array_filter($path, function ($component) {
            return !in_array($component, ['.'], true);
        });

        return $path;
    }
}
