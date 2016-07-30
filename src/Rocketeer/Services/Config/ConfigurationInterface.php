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

namespace Rocketeer\Services\Config;

/**
 * Interface for Configuration instances.
 */
interface ConfigurationInterface
{
    /**
     * Get an item from the collection by key.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value);

    /**
     * Replace the current configuration.
     *
     * @param array $items
     */
    public function replace(array $items);

    /**
     * @return array
     */
    public function all();
}
