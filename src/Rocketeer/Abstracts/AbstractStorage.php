<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Abstracts;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use Rocketeer\Traits\HasLocator;

/**
 * Abstract class for storage implementations.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class AbstractStorage
{
    use HasLocator;

    /**
     * The file to act on.
     *
     * @type string
     */
    protected $file;

    /**
     * Build a new ServerStorage.
     *
     * @param Container $app
     * @param string    $file
     */
    public function __construct(Container $app, $file)
    {
        $this->app  = $app;
        $this->file = $file;
    }

    /**
     * Change the file in use.
     *
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// VALUES ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get a value on the server.
     *
     * @param string|null               $key
     * @param array|string|Closure|null $fallback
     *
     * @return string|int|array
     */
    public function get($key = null, $fallback = null)
    {
        $contents = $this->getContents();

        return Arr::get($contents, $key, $fallback);
    }

    /**
     * Set a value on the server.
     *
     * @param string|array $key
     * @param mixed|null   $value
     */
    public function set($key, $value = null)
    {
        // Set the value on the contents
        $contents = (array) $this->getContents();
        if (is_array($key)) {
            $contents = $key;
        } else {
            Arr::set($contents, $key, $value);
        }

        $this->saveContents($contents);
    }

    /**
     * Forget a value from the repository file.
     *
     * @param string $key
     */
    public function forget($key)
    {
        $contents = $this->getContents();
        Arr::forget($contents, $key);

        $this->saveContents($contents);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the full path to the file.
     *
     * @return string
     */
    abstract public function getFilepath();

    /**
     * Get the contents of the file.
     *
     * @return array
     */
    abstract protected function getContents();

    /**
     * Save the contents of the file.
     *
     * @param array $contents
     */
    abstract protected function saveContents($contents);
}
